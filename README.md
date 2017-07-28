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

db_gems folder hosts the Rahl Commander DB assets and tests.
bin folder is for backend processes / cli tools.
doors folder is to handle requests from other apps/clients.
application/api is where we define the Actions (good idea to have one abstract action per folder, if all actions in folder share same dependencies/filters)  
application/model is where we model the business logic in a middleware way + auziliary helper classes, like IDUhubs  
application/lib is low level code/library, specific for this project (otherwise, consider putting it in TalisMS)  
application/aux is for auxiliary classes for specific data sources elements, like the IDUHubs  
