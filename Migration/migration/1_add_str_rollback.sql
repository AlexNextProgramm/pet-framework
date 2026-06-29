SET @db_name = (SELECT DATABASE());
SET @table_name = 'migrate';
SET @column_name = 'str_rollback';

SET @stmt = (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db_name
            AND TABLE_NAME = @table_name
            AND COLUMN_NAME = @column_name
        ),
        'SELECT 1',
        CONCAT('ALTER TABLE `', @table_name, '` ADD `', @column_name, '` TEXT NULL DEFAULT NULL AFTER `sql_str`')
    )
);
PREPARE stmt FROM @stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
# Rollback >>> ALTER TABLE `migrate` DROP `str_rollback`;