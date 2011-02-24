CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%faq_questions (
	faq_id int(11) unsigned NOT NULL AUTO_INCREMENT,
	faq_group int(11) NOT NULL,
	faq_question varchar(255) NOT NULL,
	faq_answer text NOT NULL,
	faq_order int(11) NOT NULL,
	PRIMARY KEY (faq_id)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%faq_categories (
	faq_cat_id int(11) unsigned NOT NULL AUTO_INCREMENT,
	faq_cat_name varchar(255) NOT NULL,
	faq_cat_order int(11) NOT NULL,
	PRIMARY KEY (faq_cat_id)
);
