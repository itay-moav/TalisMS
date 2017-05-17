# TalisMS
TalisMS is a PHP framework for fast prototyping systems in a Micro Service eco system

Folder structure
================
Talis  - library/framework code  
public - doc root for web servers  
init   - bootstrap/config code  
environemnt - environment files (dev/qa/production etc)  
application - business specific code, not part of the library  
tests  - utilities to qa/test your code   
tests/lib - some utilities to enable quick demo writing  
application/api - Each object here represents an API call, this is the application entry point  
application/model - business logic objects to support the api objects  
application/omega_supreme - database objects (stored procedures/triggers/function/views) -> builds through Rahl Commander  

