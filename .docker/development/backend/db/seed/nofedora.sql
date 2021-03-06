REPLACE INTO fez_auth_rules (ar_id, ar_rule, ar_value)
VALUES
	(1, 'public_list', 1),
	(2, '!rule!role!Fez_Group', 1),
	(3, '!rule!role!Fez_Group', 2),
	(4, '!rule!role!Fez_Group', 3);

REPLACE INTO fez_auth_rule_groups (arg_id, arg_md5)
VALUES
  (1, '833941f9ed133983793771848340608a'),
 	(2, 'b399b33194dd9c343e41f3e384dbf71d'),
 	(3, '9a03c67c7d5eeb5d76d6efed821a18c9'),
 	(4, '84c0c94349778fdbdf72b85123cc51f9');

REPLACE INTO fez_auth_rule_group_rules (argr_arg_id, argr_ar_id)
 VALUES
  (1, 1),
	(2, 2),
	(3, 3),
	(4, 4);

REPLACE INTO fez_auth_quick_rules (qac_id, qac_aro_id, qac_arg_id)
VALUES
	(1, 10, 2),
	(2, 10, 3),
 	(3, 10, 4);
