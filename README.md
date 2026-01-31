Backward Compatibility Plugin Enabler

A one-time-use PHP recovery script for Joomla websites.
It restores backward compatibility plugins directly from the database when the Joomla frontend or admin panel is inaccessible.

This tool is intended for emergency recovery only.

🎯 Use Cases

Joomla site breaks after disabling compatibility plugin

Joomla administrator panel not accessible

phpMyAdmin available but backend UI not working

Quick database-level recovery without manual edits

⚙️ How to Use
1. Upload Script

Upload backward-compatibility-enabler.php to your Joomla root directory.

2. Open in Browser

Access the script:

https://yourdomain.com/backward-compatibility-enabler.php


You will see a message like:

Plugins enabled: X


Where X is the number of plugins successfully enabled.

3. Delete Script

Click the Delete This Script button on the page to remove it automatically.
Alternatively, manually delete the file from the server.

🔒 Security Notes

Script runs only once using a lock file .bcpe.lock

Self-delete button ensures one-time execution

Do not keep this file on production servers after use

Intended strictly for emergency Joomla recovery

✅ Compatibility

Joomla 3.x

Joomla 4.x

Joomla 5.x

PHP 7.2 and above
