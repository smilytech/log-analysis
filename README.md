# Legal One BE Coding Challenge

Thanks for going through our interview process and for taking the coding test!

## Problem description

There is an aggregated log file that contains log lines from multiple services. This file is potentially very large (think hundreds of millions of lines). A very small example of this file can be found in the provided `logs.txt`.

We would now like to analyze this file, e.g. count log lines for a specific service.

## Tasks

1. Build a console command that parses the file and inserts the data to a database (without using a parsing library). Pick any DB that you are familiar with and decide on a schema. The import should be triggered manually, and it should be able to start where it left off when interrupted.

2. Build a RESTful service that implements the provided `api.yaml` OpenAPI specs.

The service should query the database and provide a single endpoint `/count` which returns a count of rows that match the filter criteria.

   a) This endpoint accepts a list of filters via GET request and allows zero or more filter parameters. The filters are:
   - serviceNames
   - statusCode
   - startDate
   - endDate
   
   b) Endpoint result:

```
{
    "counter": 1
}
```

## Submit your solution

Please create a repository on Github, Gitlab or BitBucket with your solution.

Implement the solution in **PHP 8.1**, using the **Symfony** framework and document the solution in the README file. You may use the template app provided in the `devbox` folder (see included README file for details). 

Once you are done with the challenge, please send us the link to the repo.

Wishing you the best.

## Please note

- For testing purposes take a look at our example log file `logs.txt`. It only contains 20 entries. Your submission will be evaluated with a much larger file (~ hundreds of millions of lines)
- If you feel there are ambiguities in the requirements feel free to document them in the README file

## What we are looking at

1. Correctness
2. SOLID / DRY / KISS
3. Tests
4. Agnostic / Reusable Code
5. Design patterns
6. Documentation of the solution

## SOLUTION DOCUMENTATION

### How it Works
A Single Database has been created for the implementation of this coding challenge.
Each record represents a single line entry in the processed log file. For the command Line
Operation

- ### Console Command
    The Console command makes use of 3 main components:
    - LogService
    - EntityManager
    - Cache Engine (Default: FileSystem Cache)
    
    The console command requires one argument to be run which is the location of the log to be processed.
    
    To start the console operation, run the command below from the root folder of the application:
    
    ```
    php bin/console log:process {file_location}
    ```
  
    Example:
    
    ```
    php bin/console log:process ./../logs.txt
    ```
    
    Once the command is ran, the log processing operation starts if the provided log file exists.
    
    Logs in the file are read line by line and saved in batches of 20 records - i.e. after 20 records
    have been processed, the log details are now committed to the DB; while this happens the Cache Engine
    is used to keep track of the **lastPosition** of the file pointer as records are processed and saved - this allows 
    the app to resume where it left off if interrupted.
    
- ### Restful Service
    The `/count` endpoint has also been implemented just as described with the option to query using the 
    provided params (serviceNames, statusCode, startDate, endDate) - validation also occurs to ensure 
    appropriate values are provided.
    
    Sample Request:
    
    ```
    localhost:8000/count?statusCode=201&serviceNames[]=USER-SERVICE&endDate=2021-08-24 09:22:59
    ```
  
    Sample Response (Success):
    ```
      {
          "counter": 11
      }
    ```
  
    Sample Response (Bad Request):
    ```
     {
         "endDate": "End Date is not valid, expected format is: 'Y-m-d H:i:s'"
     }
    ```
    
    In the case of an invalid param being passed a `Bad Request` status code and message is returned.

### DB DESIGN
Each row in the DB table represents a line in the log file.
Below is the DB structure:

- **DB Name**: `log_processor`
- **Table Name**: `log`
- **Table Columns**:
    - **`id`**: The primary key columns for each record entry
    - **`service_name`**: holds the service name extracted from the log
    - **`date`**: holds the date value extracted from the log
    - **`request_type`**: holds the request method value extracted from the log
    - **`endpoint`**: holds the request endpoint value extracted from the log
    - **`http_version`**: holds the http_version of the request extracted from the log
    - **`response_code`**: holds the request response code value extracted from the log
