<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Console;

use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use Nette\DI\Attributes\Inject;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadFeeds extends BaseCommand
{
    #[Inject]
    public Container $container;

    protected static $defaultName = 'app:download-feeds';

    protected static $defaultDescription = 'Download feeds from the source';
    private SymfonyStyle $io;
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $tempDir = __DIR__ . '/../../temp/xml';
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0777, true)) {
                $this->io->error('Directory ' . $tempDir . ' was not created');
                return Command::FAILURE;
            }
        }

        $urls = $this->container->getParameters()['urls'];

        $this->io->title('Starting Feed Downloads');
        $this->io->progressStart(count($urls));

        $updateFiles = [];
        $notUpdatedFiles = [];
        $errorFiles = [];
        $client = new Client(
            [
                'timeout' => 300,
                'verify' => false
            ]
        );

        foreach ($urls as $id => $value) {
            $this->io->newLine();
            $this->io->section('Proccessing ' . $value['name']);

            try {
                $filepath = $tempDir . '/' . $this->sanitizeFileName($value['name']) . '.xml';

                if ($this->shouldUpdateFile($filepath)) {
                    $size = $this->getRemoteFileSize($client, $value['url']);

                    if ($size === null) {
                        $this->io->warning('Could not determine file size for ' . $value['name']);
                        $this->downloadFileWithoutSize($client, $value, $filepath);
                    } else {
                        $this->downloadFileWithProgress($client, $value, $filepath, $size);
                    }

                    $updateFiles[] = $value['name'];
                    $this->io->success("Downloaded: {$value['name']}");
                } else {
                    $notUpdatedFiles[] = $value['name'];
                    $this->io->note("Skipped (up to date): {$value['name']}");
                }
            } catch (\Exception $e) {
                $errorFiles[] = [
                    'name' => $value['name'],
                    'error' => $e->getMessage()
                ];
                $this->io->error("Error: {$value['name']} - {$e->getMessage()}");
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->newLine(2);

        $this->displaySummary($updateFiles, $notUpdatedFiles, $errorFiles);

        return empty($errorFiles) ? Command::SUCCESS : Command::FAILURE;

    }

    private function shouldUpdateFile(string $filepath): bool
    {
        return !file_exists($filepath) || filemtime($filepath) < time() - 72000;
    }

    private function sanitizeFileName(string $filename): string
    {
        $filename =  iconv('UTF-8', 'ASCII//TRANSLIT', $filename);
        return preg_replace('/[^a-zA-Z0-9-_.]/', '_', $filename);
    }

    /**
     * Pokusí se zjistit velikost souboru na vzdáleném serveru
     *
     * @param Client $client
     * @param string $url
     * @return int|null
     */
    private function getRemoteFileSize(Client $client, string $url): ?int
    {
        try {
            $response =  $client->head($url);
            return (int) $response->getHeader('Content-Length');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Stáhne soubor, v průběhu stahování zobrazuje progressbar
     *
     * @param Client $client
     * @param array $value
     * @param string $filepath
     * @param int $totalSize
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function downloadFileWithProgress(Client $client, array $value, string $filepath, int $totalSize): void
    {
        $progress = $this->io->createProgressBar($totalSize);
        $progress->setFormat(
            ' %current%/%max% bytes [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
        );
        $progress->start();

        $tempFilePath = $filepath . '.temp';
        file_put_contents($tempFilePath, '');
        $response = $client->request('GET', $value['url'], [
            'stream' => true, //Tady by se dal klidně použít sink, který by měl být rychlejší. Nicméně požadavkem bylo použití streamu
            'progress' => function($downloadTotal, $downloadedBytes) use ($progress) {
                $progress->setProgress($downloadedBytes);
            }
        ]);

        $stream = $response->getBody();

        while (!$stream->eof()) {
            $chunk = $stream->read(8192);
            file_put_contents($tempFilePath, $chunk, FILE_APPEND);
        }

        rename($tempFilePath, $filepath);
        $progress->finish();
        $this->io->newLine();
    }

    /**
     * Stáhne soubor bez zobrazení progressbaru, průběžně zobrazuje velikost stahovaného souboru
     *
     * @param Client $client
     * @param array $value
     * @param string $filepath
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function downloadFileWithoutSize(Client $client, array $value, string $filepath): void
    {
        $downloadedBytes = 0;
        $tempFilePath = $filepath . '.temp';
        file_put_contents($tempFilePath, '');

        $response = $client->request('GET', $value['url'], ['stream' => true]);

        $stream = $response->getBody();

        while (!$stream->eof()) {
            $chunk = $stream->read(8192);
            $downloadedBytes += strlen($chunk);
            file_put_contents($tempFilePath, $chunk, FILE_APPEND);
            $this->io->write("\rDownloaded: " . $this->formatBytes($downloadedBytes));
        }

        rename($tempFilePath, $filepath);
        $this->io->newLine();
    }

    /**
     * Formátuje velikost souboru
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /=  pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Vypíše finální statistiky
     *
     * @param array $updateFiles
     * @param array $notUpdatedFiles
     * @param array $errorFiles
     */
    private function displaySummary(
        array $updateFiles,
        array $notUpdatedFiles,
        array $errorFiles
    ): void {
        $this->io->section('Download Summary');

        if (!empty($updateFiles)) {
            $this->io->success([
                'Updated files:',
                ...array_map(fn($file) => "- $file", $updateFiles)
            ]);
        }

        if (!empty($notUpdatedFiles)) {
            $this->io->note([
                'Skipped files (up to date):',
                ...array_map(fn($file) => "- $file", $notUpdatedFiles)
            ]);
        }

        if (!empty($errorFiles)) {
            $this->io->error([
                'Failed downloads:',
                ...array_map(
                    fn($file) => "- {$file['name']}: {$file['error']}",
                    $errorFiles
                )
            ]);
        }

        // Statistiky
        $this->io->table(
            ['Metric', 'Count'],
            [
                ['Total files', count($updateFiles) + count($notUpdatedFiles) + count($errorFiles)],
                ['Updated', count($updateFiles)],
                ['Skipped', count($notUpdatedFiles)],
                ['Failed', count($errorFiles)]
            ]
        );
    }
}