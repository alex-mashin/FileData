# FileData
A *MediaWiki* extension providing parser function `{{#file_data:}}` returning uploaded file's metadata.

## Installation
```bash
cd mediawiki/extensions
git clone https://github.com/alex-mashin/FileData.git
```

## Enabling
Add to `LocalSettings.php`:
```php
wfLoadExtension( 'FileData' );
```

## Usage:
```
Width: {{#file_data:Some uploaded file.png|width}}
```

Allowed attributes:
 - `name`,
 - `size`,
 - `width`,
 - `height`,
 - `bits`,
 - `media_type`,
 - `major_mime`,
 - `minor_mime`,
 - `timestamp`,
 - `sha1`,
 - `description`,
 - `user`,
 - `user_text`,
 - `metadata`,
 - *whatever fields are present in the image metadata*.
