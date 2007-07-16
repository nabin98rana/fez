ALTER TABLE `%TABLE_PREFIX%record_locks` 
  ADD COLUMN `rl_context_type` int(11) NOT NULL,       
  ADD COLUMN `rl_context_value` int(11) NOT NULL;
