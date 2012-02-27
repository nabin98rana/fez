UPDATE %TABLE_PREFIX%controlled_vocab
SET cvo_desc = ""
WHERE cvo_desc IS NULL;
