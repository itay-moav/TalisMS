Errors as opposed to Dependencies are simple Renderable objects.  
They do not validate but they do maintain the chain.  
They emit the Response as error+ error message + original body to the client.  
BEWARE! If you have a chain that might decide in the middle to emit a not auth error to clean the body.  
In general, auth should be calculated in the start