<?php

namespace App\Services\Backup;

/**
 * Writes a temporary --defaults-extra-file for the mysql/mysqldump CLI clients
 * so the password never appears in argv (visible to any user via `ps` on a
 * shared host). Callers must unlink() the returned path when done.
 */
class MysqlCredentialsFile
{
    public static function write(array $target): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mysql_creds_');
        chmod($path, 0600);

        file_put_contents($path, sprintf(
            "[client]\nuser=%s\npassword=%s\nhost=%s\nport=%s\n",
            $target['username'],
            $target['password'],
            $target['host'],
            $target['port'],
        ));

        return $path;
    }
}
