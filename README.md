% RelaxCMS简介
% 版本：0.8.14 / 更新时间：2023.8.31



前言
--------

本文主要介绍系统架构、系统特性、开发环境、运行环境、构建编译、模板制作。主要阅读对象为二次开发人员。

*注1：本文档内容依据版本变化将会即时修订，当手册描述与软件实现不一致时，请下载或登录系统查看与软件版本一致的最新手册。*


快速入门
--------
RelaxCMS是一款基于PHP开发的WEB应用框架,主要用于构建WEB应用系统，如：WEB门户、WEB管理系统及API接口等服务系统。



#系统架构

![系统架构](img/arch.png)

#系统特性
- 跨平台
- 多数据库

	支持：PDO/MYSQL/SQLITE/POSTGRESQL/MONGODB/MSSQL/ORACLE
		
- 多语言
	
	支持多语种国际化：中文（简、繁）、英语等
	
- 可扩展

	支持第三方应用扩展安装。

- 多模板

	支持多模板布局

- 多主题

	支持多主题风格

#开发环境
	PHP/MYSQL
	
#运行环境

	类LAPM集成环境，如LAPM、CRAB; 推荐使用CRAB搭建服务器环境。

#源码下载

- RELAXCMS 官网

		https://www.relaxcms.com
		
- RELAXCMS 演示

		https://demo.relaxcms.com		

- GITHUB下载

		svn co https://github.com/relaxcms/relaxcms.git

#一键安装

-- 支持LINUX环境下一键安装，推荐使用Ubuntu 16.04/18.04 Server LTS


		if [ -f /usr/bin/curl ];then curl -sSLO https://www.relaxcms.com/install/install.sh;else wget -O install.sh https://www.relaxcms.com/install/install.sh;fi;bash install.sh
		

-- 注：默认安装RelaxCMS官网发布的最新稳定版本		

#快速安装

-- 从RelaxCMS官网下载RelaxCMS 最新版本，如：https://www.relaxcms.com/f/35/relaxcms-0.8.14.962.tar.gz

-- 从RelaxCMS官网下载RelaxCMS最新版本，运行环境CRAB: 

	LINUX版本：https://www.relaxcms.com/f/17/crab-0.8.4.51-x86_64.tar.gz，下载后，解压安装，./setup.sh命令安装。
	
	WINDOWS版本：https://www.relaxcms.com/f/38/crab-0.8.3.28-win32.exe，下载后，点击安装，开发调试使用，生产环境不建议用。




#源码目录结构说明

![源码截图](img/srcdir.png)

	src/
	- apps 应用目录
	  |
	  |- system                 
	       |- components        应用组件
	       |- database          应用数据库 
	       |- docs              应用文档
	       |- i18n              应用国际化配置
	       |- includes          应用菜单
	       |- models            应用模型
	       |- templates         应用模板
	       |- config.php        应用配置
	       |_ system.php        应用类，名称格式：名称+.php，必须从CApplication派生
	       
	- docs 文档
	- lib  库(核心架构类库）
	- modules 嵌入模块
	- public WEB公开及静态文件，通用DocumentRoot设置为此目录
	- supports 第三方开源库
	- templates 模板
	- cache 缓存
	- config 配置
	
	

	
	
  