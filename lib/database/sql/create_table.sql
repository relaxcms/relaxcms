
-- create_table.sql

create table cms_user (
  id int NOT NULL,
  uid int NOT NULL comment 'UID',
  name varchar(64) NOT NULL comment '用户名',
  description text NULL,  
  password varchar(32) NOT NULL,
  nickname varchar(32) null comment '昵称',
  email varchar(64) NULL,
  avatar varchar(256) NULL,
  rid int not NULL,
  oid int not NULL,
  flags int not NULL,
  type int not null, 
  last_time int null,
  last_ip varchar(32) null,
  fails int not null default 0,
  logins int not null default 0,
  ts bigint not null,
  pwd_last_update_ts int not null,
  last_pwd text null,
  allow_ip text null,
  status int not null,
  UNIQUE(uid),
  UNIQUE(name),
  primary key(id)
);


create table cms_user_account (
  id int NOT NULL,
  uid int NOT NULL comment 'UID',
  account varchar(32) NOT NULL comment '帐号',
  type int not null, 
  status int not null, 
  UNIQUE(account),
  primary key(id)
);

create table cms_user_seccode (
  id int NOT NULL,
  secid	varchar(64)	not null,
  seccode	varchar(64)	not null,
  ts	bigint	not null,
  ttl	int	not null, 
  UNIQUE KEY (secid), 
  primary key(id)
);


create table cms_user_token (
  id int NOT NULL,
  uid int NOT NULL,
  token varchar(64) NOT NULL,
  secret varchar(256) NOT NULL,
  UNIQUE KEY (uid), 
  UNIQUE KEY (token), 
  primary key(id)
);

-- 用户会话表
create table cms_session (
    id int not null,
    ssid varchar(64) not null,
    uid int not null,
    model varchar(32) not null,
    cktime int not null default 0,
    login_ip varchar(40) null,
    login_type varchar(32) null,
    login_ts int NOT NULL,
    expire_ts int NOT NULL,
    hkey char(32) not null,    
    UNIQUE KEY (ssid), 
	primary key(id)
);

create table  cms_group(
  id int NOT NULL,
  name varchar(64) NOT NULL,
  description varchar(256) NULL,
  type tinyint NOT NULL,
  UNIQUE KEY(name),
  primary key(id)
);


create table cms_privilege(
 id int NOT NULL,
 name varchar(64) not null,
 description varchar(256) not null,
 component varchar(32) not null,
 pid int NOT NULL,
 UNIQUE KEY(component),
 primary KEY(id)
);

create table cms_privilege2group(
 id int not null,
 pid int NOT NULL,
 gid int not null,
 permision int default 7,
 UNIQUE key(pid,gid),
 primary KEY(id)
);

create table cms_role(
  id int NOT NULL,
  name varchar(64) NOT NULL,
  description varchar(256) NULL,
  type int NOT NULL,
  level int NOT NULL,
  UNIQUE KEY(name),
  primary key(id)
);

create table cms_group2role(
 id int not null,
 gid int NOT NULL,
 rid int not null,
 UNIQUE KEY(gid,rid),
 primary key(id) 
);




create table cms_log (
	id int not null,
	ts bigint not null,
	ip varchar(40) NOT NULL,
	description text null,
	errno int not null,
	uid int not null,
	status int not null,
	modname varchar(32) null,
	mid int null,
	action varchar(32) null,
	level int null default 7,
	oldobj text null,
	newobj text null,
	primary key(id)
);

create table cms_msg(
 id	int	not null,
 name varchar(64) not null,
 description text null,
 type  int not null,
 level int not null,
 flags int not null,
 cuid int not null,
 ctime bigint not null,
 sends int not null,
 opened int not null,
 status	int	not null,
 primary KEY(id)
 );

create table cms_msg2user(
  id int not null,
  mid int not null,
  uid int not null,
  status int not null,
  primary KEY(id)
);



-- var
create table cms_var (
  id int NOT NULL,
  name varchar(32) not null default '',
  value varchar(128) not null default '',
  title varchar(128) null,
  attr int null,
  taxis tinyint null,
  pid int NOT NULL default 0,
  PRIMARY KEY (id)
);

-- file
create table cms_file (
  id int NOT NULL,
  name varchar(256) NOT NULL,
  filename varchar(256) NULL,
  fileid varchar(64) NULL,
  path varchar(256) NULL,
  extname varchar(16) NOT NULL,
  type int NOT NULL,
  size bigint NOT NULL,
  description text null,
  width int null default 0,
  height int null default 0,
  downloads int null default 0,
  hits int null default 0,  
  gid int not null default 0,
  oid int not null default 0,
  sid  int  not null,
  cuid int not null,
  ctime bigint  NULL,
  uid int not null,
  ts int not null,
  is_default int not null default 0,
  isdir int not null default 0,
  taxis int NOT NULL default 0,
  uses int null default 0,
  pid int not null default 0,
  convert_id int not null default 0,
  snap_id int not null default 0,  
  flags int null default 0,
  status int not null default 0,
  INDEX(type),
  UNIQUE KEY(pid,name),
  UNIQUE KEY(path),
  UNIQUE KEY(filename),
  primary key(id)
);

create table cms_file2model (
  id int NOT NULL,
  fid int NOT NULL,
  modname varchar(64) null,
  mid int not null default 0,
  title	varchar(128)	NULL,
  description	text	NULL,
  taxis	int	NULL,
  linkurl	varchar(256)	NULL,
  num int not null default 0,
  checked int not null default 0,
  UNIQUE KEY(fid,modname,mid),
  primary key(id)
);

-- Server
create table cms_server(
	id int not null,
	name varchar(64) not null,
	description text null,
	ip varchar(64) null,
	webrooturl varchar(128) null,
	rtmprooturl varchar(128) null,
	hlsrooturl varchar(128) null,
	vodrooturl varchar(128) null,
	ts bigint not null,
	flags int not null default 0,
	status int not null default 0,
	primary key(id)
);

-- storage
create table cms_storage (
  id int NOT NULL,
  name varchar(64) NOT NULL,
  stype tinyint NOT NULL,
  spath varchar(256) NULL,
  username varchar(32) NULL,
  password varchar(32) NULL,
  mountdir varchar(256) NOT NULL,
  webpath varchar(256) NULL,
  sid int NULL,
  total bigint NOT NULL,
  used bigint not null,
  status int NOT NULL,
  UNIQUE KEY(webpath),
  primary key(id)
);

-- app
create table cms_app(
 id int not null,
 title varchar(64) not null,
 description text null,
 version varchar(64) not null,
 name varchar(64) not null,
 appname varchar(64) not null,
 appid varchar(64) not null,
 logo varchar(256) NULL,
 developer varchar(64)	NULL,
 language int	NOT NULL,
 url varchar(256) NULL,
 platform int NOT NULL,
 type int NOT NULL,
 public int NOT NULL,
 uid  int NOT NULL,
 bean int NOT NULL,
 embeded int not null,
 uninstall int not null,
 local int not null,
 remote int not null,
 remote_version varchar(64) not null,
 remote_download_url varchar(256) NULL,
 rkey int NOT NULL,
 copyright text null,
 installed int not null,
 installdir varchar(256) null,
 ctime	bigint	NOT NULL,
 ts	bigint	NOT NULL,
 status	int	NOT NULL,
 UNIQUE (appid),
 UNIQUE KEY (name),
 PRIMARY KEY (id)
);

