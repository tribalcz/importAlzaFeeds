<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Console;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Price2Performance\Price2Performance\Services\CategoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class ImportProductsToDatabase extends BaseCommand
{
    protected static $defaultName = 'app:import-products';

    private Explorer $database;
    private CategoryManager $categoryManager;
    private array $processedProductsIds = [];
    private const BATCH_SIZE = 1000;

    public function __construct(Explorer $database, CategoryManager $categoryManager)
    {
        parent::__construct();
        $this->database = $database;
        $this->categoryManager = $categoryManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Import products to the database');
        $this->addArgument('directory', InputArgument::OPTIONAL, 'Path to the directory with XML files', 'temp/xml');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $directory = $input->getArgument('directory');

        if (!is_dir($directory)) {
            $io->error('Directory ' . $directory . ' does not exist');
            return Command::FAILURE;
        }

        try {
            $finder = new Finder();
            $finder->files()->in($directory)->name('*.xml');

            if (!$finder->hasResults()) {
                $io->error('No XML files found in ' . $directory);
                return Command::FAILURE;
            }

            $stats = [
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0
            ];


            $this->database->beginTransaction();
            foreach ($finder as $file) {
                $xmlFile = $file->getRealPath();
                $io->note('Processing file: ' . $xmlFile);

                $dom = new \DOMDocument();
                $success = $dom->load($xmlFile);

                if (!$success) {
                    $io->error('Failed to parse XML file: ' . $xmlFile);
                    continue;
                }

                $items = $dom->getElementsByTagName('item');
                foreach($items as $item){
                    try {
                        $productData = $this->extractProductData($item);
                        $this->processProduct($productData, $stats);

                        $stats['processed']++;

                        if ($stats['processed'] % self::BATCH_SIZE === 0) {
                            $io->writeln(sprintf(
                                'Processed: %d, Created: %d, Updated: %d, Skipped: %d',
                                $stats['processed'],
                                $stats['created'],
                                $stats['updated'],
                                $stats['skipped']
                            ));
                            $this->database->commit();
                            $this->database->beginTransaction();
                        }
                    } catch (\Exception $e) {
                        $io->error('Error processing product: ' . $e->getMessage());
                        continue;
                    }
                }
            }
            if (!$this->database->getConnection()->getPdo()->inTransaction()) {
                $this->database->beginTransaction();
            }
            $this->deactivateOldProducts();
            $this->database->commit();

            $io->success(sprintf(
                'Import completed. Processed: %d, Created: %d, Updated: %d, Skipped: %d',
                $stats['processed'],
                $stats['created'],
                $stats['updated'],
                $stats['skipped']
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            if ($this->database->getConnection()->getPdo()->inTransaction()) {
                $this->database->rollBack();
            }
            $io->error('Error importing products: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Extrahuje data produktu z XML elementu
     *
     * @param \DOMElement $item
     * @return array
     */
    private function extractProductData(\DOMElement $item): array
    {
        $xpath = new \DOMXPath($item->ownerDocument);
        $xpath->registerNamespace('g', 'http://base.google.com/ns/1.0');

        $getValue = function($query) use ($xpath, $item) {
            $nodes = $xpath->evaluate($query, $item);
            return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
        };

        $processPrice = function($price) {
            if (empty($price)) {
                return 0.0;
            }
            $price = preg_replace('/[^0-9,.]/', '', $price);
            return (float) str_replace(',', '.', $price);
        };

        try {
            return [
                'title' => $getValue('title'),
                'product_id' => $getValue('g:id'),
                'product_condition' => $getValue('g:condition'),
                'description' => $getValue('description'),
                'link' => $getValue('link'),
                'image_link' => $getValue('g:image_link'),
                'brand' => $getValue('g:brand'),
                'gtin' => $getValue('g:gtin'),
                'mpn' => $getValue('g:mpn'),
                'availability' => $getValue('g:availability'),
                'price' => $processPrice($getValue('g:price')),
                'product_type' => $getValue('g:product_type')
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to extract product data: " . $e->getMessage() .
                "\nProduct title: " . $getValue('title')
            );
        }
    }

    private function processProduct(array $productData, array &$stats): void
    {
        $category = $this->categoryManager->getCategoryFromPath($productData['product_type']);
        $productData['category_id'] = $category['id'];

        $this->processedProductsIds[] = $productData['product_id'];

        $existingProduct = $this->database->table('product')
            ->where('gid', $productData['product_id'])
            ->fetch();

        if (!$existingProduct) {
            $this->database->table('product')->insert([
                'title' => $productData['title'],
                'gid' => $productData['product_id'],
                'product_condition' => $productData['product_condition'],
                'description' => $productData['description'],
                'link' => $productData['link'],
                'image_link' => $productData['image_link'],
                'brand' => $productData['brand'],
                'gtin' => $productData['gtin'],
                'mpn' => $productData['mpn'],
                'availability' => $productData['availability'],
                'price' => $productData['price'],
                'category_id' => $category['id'],
                'active' => true
            ]);
            $stats['created']++;
            return;
        }

        if($this->hasProductChanged($existingProduct, $productData)) {
            $existingProduct->update([
                'title' => $productData['title'],
                'product_condition' => $productData['product_condition'],
                'description' => $productData['description'],
                'link' => $productData['link'],
                'image_link' => $productData['image_link'],
                'brand' => $productData['brand'],
                'gtin' => $productData['gtin'],
                'mpn' => $productData['mpn'],
                'availability' => $productData['availability'],
                'price' => $productData['price'],
                'category_id' => $category['id'],
                'active' => true
            ]);
            $stats['updated']++;
        } else {
            $existingProduct->update(['active' => true]);
            $stats['skipped']++;
        }

        if ($stats['processed'] % self::BATCH_SIZE === 0) {
            //$this->categoryManager->flush();
        }
    }

    private function hasProductChanged(ActiveRow $existingProduct, array $newData): bool
    {
        $relevantFields = [
            'title' => 'title',
            'gid' => 'product_id',
            'product_condition' => 'product_condition',
            'description' => 'description',
            'link' => 'link',
            'image_link' => 'image_link',
            'brand' => 'brand',
            'gtin' => 'gtin',
            'mpn' => 'mpn',
            'availability' => 'availability',
            'price' => 'price',
            'category_id' => 'category_id'
        ];

        foreach ($relevantFields as $dbField => $dataField) {
            if ((string)$existingProduct[$dbField] !== (string)$newData[$dataField]) {
                return true;
            }
        }

        return false;
    }

    private function deactivateOldProducts(): void
    {
        if (empty($this->processedProductIds)) {
            return;
        }

        $this->database->table('product')
            ->where('gid NOT IN ?', $this->processedProductIds)
            ->update([
                'active' => false
            ]);
    }
}