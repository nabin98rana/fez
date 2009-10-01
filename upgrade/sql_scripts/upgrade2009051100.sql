create table if not exists %TABLE_PREFIX%statistics_sum_yearmonth
(
	sym_year char(4) not null,
	sym_month char(2) not null,
	sym_pid varchar(64) not null,
	sym_title varchar(255) not null,
	sym_downloads int not null,
	sym_citation text,
	primary key(sym_year, sym_month, sym_pid)
);

create table if not exists %TABLE_PREFIX%statistics_sum_countryregion
(
	scr_id int not null primary key auto_increment,
	scr_country_name varchar(50),
	scr_country_code varchar(4),
	scr_country_region varchar(50),
	scr_city varchar(255),
	scr_count_abstract int,
	scr_count_downloads int
);

create table if not exists %TABLE_PREFIX%statistics_sum_authors
(
	sau_author_id int not null primary key,
	sau_author_name varchar(255),
	sau_downloads int
);


create table if not exists %TABLE_PREFIX%statistics_sum_papers
(
	spa_pid varchar(64) not null primary key,
	spa_title varchar(255) not null,
	spa_citation text not null,
	spa_downloads int not null
);

create table if not exists %TABLE_PREFIX%statistics_sum_4weeks
(
	s4w_pid varchar(64) not null primary key,
	s4w_title varchar(255) not null,
	s4w_citation text not null,
	s4w_downloads int not null
);

