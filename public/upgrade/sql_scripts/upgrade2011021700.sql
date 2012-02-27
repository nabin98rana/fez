CREATE TABLE %TABLE_PREFIX%faq_questions (
	faq_id serial NOT NULL PRIMARY KEY,
	faq_group integer NOT NULL,
	faq_question varchar(255) NOT NULL,
	faq_answer text NOT NULL,
	faq_order integer NOT NULL
);

CREATE TABLE %TABLE_PREFIX%faq_categories (
	faq_cat_id serial NOT NULL PRIMARY KEY,
	faq_cat_name varchar(255) NOT NULL,
	faq_cat_order integer NOT NULL
);
