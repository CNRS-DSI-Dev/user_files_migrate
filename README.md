# User Files Migrate

Owncloud 7 app that allows a user to request for migrating files between between two owncloud accounts.

The user *must* be able to connect on both account. He will be presented a section in personnal admin page that will allow to first create, then confirm  a files migration request.

**The user custom parameters and datas (as contact datas, tags metadatas, share infos, etc.) will NOT be migrated. Only the files will be.**

## Steps to request a files migration

- The user must connect to the first account, go to the personnal admin page, the, request a files migration to another account login.
- The user must connect to the second account, go to the personnal admin page, then confirm that the requested datas migration is valid.
- The files migration will be processed in batch mode.

## Configuration

The files migration process is use the app's command line utility. For the needs of CNRS organisation, some tasks are launched at the end of it :
- a mail is sent to the user 
- a mail is sent to a monitoring team. The monitoring mail address has to be set in `config.php`
- a mail is sent to the target account's main group admin (more below)
- the first account is added to a specific owncloud group, depending on the first account's main group (more below)
These tasks should be configured in config.php

```php
'migration_admin_email' => 'monitoring@yourdomain.tld',
'migration_admin_emails' => array(
  'maingroup1' => 'maingroup1_admins@yourdomain.tld',
  'maingroup2' => 'maingroup2_admins@yourdomain.tld',
),
'migration_default_admin_email' => 'maingroup_admins@yourdomain.tld',
'migration_exclusion_groups' => array(
  'maingroup1' => 'maingroup1_specific',
  'maingroup2' => 'maingroup2_specific',
),
'migration_default_exclusion_group' => 'maingroup_specific',
```

Here at CNRS organisation, each user is member of a hierarchy of groups. The main group is the top-level group in this hierarchy.

The "From" mail address have to be configured in your admin panel. See http://doc.owncloud.org/server/7.0/admin_manual/configuration/configuration_mail.html

The body of each mails may be changed : modify the templates (text and html) which are in the `templates` app directory. The template's names are pretty straightforward.

## Processing migration requests

A command line utility is provided via occ. It's the only way to process confirmed migration requests.
The utility may list the pending requests

```sh
./occ help user_files_migrate:migrate
./occ user_files_migrate:migrate -l
```

or process them (you have to sudo webserver's user to keep filesystem rights)

```sh
sudo -u apache ./occ help user_files_migrate:migrate
```

## Contributing

This app is developed for an internal deployement of ownCloud at CNRS (French National Center for Scientific Research).

If you want to be informed about this ownCloud project at CNRS, please contact david.rousse@dsi.cnrs.fr, gilian.gambini@dsi.cnrs.fr or marc.dexet@dsi.cnrs.fr

## License and authors

|                      |                                          |
|:---------------------|:-----------------------------------------|
| **Author:**          | Patrick Paysant (<ppaysant@linagora.com>)
| **Copyright:**       | Copyright (c) 2015 CNRS DSI
| **License:**         | AGPL v3, see the COPYING file.
