update %TABLE_PREFIX%fulltext_cache
set ftc_content =
REPLACE(REPLACE(REPLACE(ftc_content, '\"','\"\"'), '\n', ' '), '\t', ' ');