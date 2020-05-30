<p align="center">
<a href="https://packagist.org/packages/olafnorge/borgphp"><img src="https://poser.pugx.org/olafnorge/borgphp/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/olafnorge/borgphp"><img src="https://poser.pugx.org/olafnorge/borgphp/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/olafnorge/borgphp"><img src="https://poser.pugx.org/olafnorge/borgphp/license.svg" alt="License"></a>
</p>

# Library to execute arbitrary BorgBackup commands

[From Borg Documentation: What is BorgBackup?](https://borgbackup.readthedocs.io/en/stable/#what-is-borgbackup "What is BorgBackup?")

> BorgBackup (short: Borg) is a deduplicating backup program. Optionally, it supports compression and authenticated
> encryption.
> 
> The main goal of Borg is to provide an efficient and secure way to backup data. The data deduplication technique
> used makes Borg suitable for daily backups since only changes are stored. The authenticated encryption technique
> makes it suitable for backups to not fully trusted targets.

This library makes use of the borg binary and executes arbitrary commands through the underlying [Symfony Process
component](https://github.com/symfony/process). It's essential for the library to be able to execute the binary
therefore it needs to be installed beforehand. The library itself does not check if the command is available before
executing it and will fail with a ProcessFailedException bubbled up from the Symfony Process component.

## Installation of BorgBackup (the binary itself)

Please refer to [the official documentation](https://borgbackup.readthedocs.io/en/stable/installation.html) of Borg to
get an idea which installation method suites best for your OS. 

## Installation of the library

```bash
composer require olafnorge/borgphp
```

## Usage

The library directly passes through all command parameters and options to the underlying BorgBackup binary. It takes care
of the proper position of the parameters and options and does some validation if they align with the expected format of
BorgBackup. All you need to do is passing the parameters and options as array to the command you want to execute.

```php
<?php
// list the contents of a repository or an archive 
use olafnorge\borgphp\ListCommand;

$listCommand = new ListCommand(['<REPOSITORY_OR_ARCHIVE>']);
$contents = $listCommand->mustRun()->getOutput();
var_dump($contents);
```

## Status of implementation

For now only some commands are fully implemented because I didn't have a use case for the missing commands yet. Below
you find a list of what is done so far. As mentioned already the parameters and options are directly passed through to
the borg binary. To be able to make use of a command that fits your needs I referenced the official documentation of each
command next to it.

| Command        | Official BorgBackup Documentation |
| ------------- | ------------- |
| config      | https://borgbackup.readthedocs.io/en/stable/usage/config.html#borg-config |
| create      | https://borgbackup.readthedocs.io/en/stable/usage/create.html#borg-create |
| export-tar | https://borgbackup.readthedocs.io/en/stable/usage/tar.html#borg-export-tar |
| info | https://borgbackup.readthedocs.io/en/stable/usage/info.html#borg-info |
| init | https://borgbackup.readthedocs.io/en/stable/usage/init.html#borg-init |
| list | https://borgbackup.readthedocs.io/en/stable/usage/list.html#borg-list |

Contributions are highly welcome and appreciated. I will only add additional commands by myself if I have an own need,
but if you have a need a PR would be more than welcomed.

# License

BorgPHP is open-sourced software licensed under the [MIT license](LICENSE.md).
