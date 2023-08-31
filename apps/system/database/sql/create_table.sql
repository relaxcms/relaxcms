
-- create_table.sql

-- 管理员 cms_admin

create table cms_history (
	id int not null,
	uid int not null,
	cname varchar(64) NOT NULL,
	tname varchar(64) NOT NULL,
	ts bigint not null,
	primary key(id)
);
	

