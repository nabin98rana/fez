ALTER TABLE %TABLE_PREFIX%record_search_key_subcategory MODIFY rek_subcategory VARCHAR(255);
ALTER TABLE %TABLE_PREFIX%record_search_key_book_title DROP INDEX rek_book_title;
ALTER TABLE %TABLE_PREFIX%record_search_key_book_title DROP INDEX book_title_unique;
ALTER TABLE %TABLE_PREFIX%record_search_key_book_title MODIFY rek_book_title TEXT;
CREATE UNIQUE INDEX book_title_unique ON %TABLE_PREFIX%record_search_key_book_title (rek_book_title_pid, rek_book_title(255)); 