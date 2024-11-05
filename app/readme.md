Price2Performance Sandbox
=========================

This is a simple skeleton application for all our projects.

Requirements
------------

- Requires PHP 8.2 and Docker 


Installation
------------

The best way to install Sandbox is using Composer. To install in the current folder, use command:

	composer create-project price2performance/sandbox .


Make directories `temp/` and `log/` writable.

Then replace all `sandbox` occurence with the name of your project. In PhpStorm, you can use `CTRL`+`SHIFT`+`R` to rename all occurences:

 - `sandbox` for `project` in `composer.json`
 - `Sandbox\\` for `Project\\` in `composer.json`
 - `sandbox` for `project` in `.docker` folder
 - `Price2Performance\Price2Performance` with `Price2Performance\Project` in all files in `app/`, `tests/`, `bin/` and `config/` folders
 - `Sandbox` for `Project` in the filename of `app/Model/ORM/Entity/xml/Price2Performance.Sandbox.Model.ORM.Entity.User.dcm.xml`

Use the real name of your project, not `project`.

Web Server Setup
----------------

Run `.docker/up.bat` in terminal. On Linux or Mac, run each line from `.docker/up.bat` manually in Terminal, or create your own `.docker/up.sh` instead.

In case of ports conflicts, either kill the blocking application (usually Apache) OR modify `.docker/docker-composer.yml` on these lines:

```yaml
    ports:
      - 8085:80
      - 4435:443
```

Choose appropriate ports for you.


## Setting up secure connection on localhost

Open browser and type `https://localhost` (or `https://localhost:4435` if you modified the port settings above).

If your browser complains about unsecure connections, you have 2 options:

1) either bypass this error message and tell him to continue anyway (the exact method differs browser from browser)
2) or set out root CA certificate (located at `config/rootCA.pem`) as trusted according to this article: https://phpfashion.com/jak-zprovoznit-https-na-localhost#toc-uverime-certifikatu

Detailed instructions for Mac: https://tosbourn.com/getting-os-x-to-trust-self-signed-ssl-certificates/, for Windows: https://www.thewindowsclub.com/manage-trusted-root-certificates-windows 

## Running tests

To run tests, type in the command line:
```shell
docker exec project vendor/bin/tester tests -C
```

## Composer update on branch changing

Every time you change the git branch (e.g. from `stage2` to `master` or back again), you have to update vendor directory of composer by following command:
```shell
docker exec project composer update
```

## PHPMyAdmin

PHPMyAdmin is available at `http://localhost:8080`.