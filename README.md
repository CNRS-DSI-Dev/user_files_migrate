# User Files Migrate

Owncloud 7 app that allows a user to request for migrating files between between two owncloud accounts.

The user *must* be able to connect on both account. He will be presented a section in personnal admin page that will allows to first create, then confirm  a files migration request.

## Process

- The user must connect to the first account, go to the personnal admin page, the, request a files migration to another account login.
- The user must connect to the second account, go to the personnal admin page, then confirm that the requested datas migration is valid.
- The files migration will be processed in batch mode.

## Configuration

The files migration process is triggered from cron job. At the end of it, a mail is sent to the user and to an arbitrary mail address (for monitoring purpose). The monitoring mail address has to be set in `config.php`

```php
'monitoring_files_migrate_email' => 'monitoring@youdomain.tld',
```

The "From" mail address have to be configured in your admin panel. See http://doc.owncloud.org/server/7.0/admin_manual/configuration/configuration_mail.html

The body of both mails may be changed : modify the templates (mail and html) which are in the `templates` app directory. The template's names are pretty straightforward.

**The user custom parameters and datas (as contact datas, tags metadatas, share infos, etc.) will NOT be migrated. Only the files will be.**

## License and authors

|                      |                                          |
|:---------------------|:-----------------------------------------|
| **Author:**          | Patrick Paysant (<ppaysant@linagora.com>)
| **Copyright:**       | Copyright (c) 2015 CNRS DSI
| **License:**         | AGPL v3, see the COPYING file.
