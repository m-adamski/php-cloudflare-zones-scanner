# Cloudflare Zones Scanner

With this tool you can get a summary of all DNS entries for each domain assigned to your Cloudflare account.

## Installation

The first step to launch the tool is to download this repository. Then install the Composer packages.

```bash
$ composer install --verbose --prefer-dist --no-dev --optimize-autoloader
```

## Cloudflare API Token

In order to generate a list of domains and DNS entries, the tool needs access to your Cloudflare account.
Generate API Token with permission to read Zone and DNS entries (Zone.Zone, Zone.DNS).

The generated API Token should be entered into the `config/cloudflare.json` file.

## How to use it?

To run the tool, simply enter the command in the console:

```bash
php bin/console scan
```

A file will be created in the `data` folder with a list of all domains and DNS entries.

## License

MIT
