create table %TABLE_PREFIX%integrity_index_ghosts 
(
	pid varchar(64) not null primary key
);
create table %TABLE_PREFIX%integrity_solr_ghosts 
(
	pid varchar(64) not null primary key
);
create table %TABLE_PREFIX%integrity_solr_unspawned
(
	pid varchar(64) not null primary key
);
create table %TABLE_PREFIX%integrity_solr_unspawned_citations
(
	pid varchar(64) not null primary key
);