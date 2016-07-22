REPLACE INTO fez_auth_quick_rules_id (qai_id, qai_title, qai_inherit)
VALUES
	(1, 'Masqueraders only', 0),
	(2, 'Thesis officers only', 0),
	(3, 'UPOs only', 0);

REPLACE INTO fez_auth_rules (ar_id, ar_rule, ar_value)
VALUES
	(1, 'public_list', 1),
	(2, '!rule!role!Fez_Group', 1),
	(3, '!rule!role!Fez_Group', 2),
	(4, '!rule!role!Fez_Group', 3);

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

REPLACE INTO fez_auth_rule_groups (arg_id, arg_md5)
VALUES
	(1, '833941f9ed133983793771848340608a'),
	(2, '26e49efd03d4563b794d62f8fb07e76f'),
	(3, 'b64c91ef26b0c7a0fe33bb05e7c8870c');
