# Documentation

## Description
This application detects csv files in a folder and reads them into a database. 

It was developed in a virtual machine with the Homestead box, using Ubuntu 18.04 with PHP 7.2 and nginx 1.14.

## Setup
 - Make a copy of `App/_Config.php` and name your copy `App/Config.php`. Make any necessary configurations in this copy (database and file paths mainly). Make sure the `$uploadedFolder` property points at the folder containing the csv files.
 - Setup crontab to run on desired schedule, the script to run is `process-uploaded-files.php`.
 
### Note
Config was made as a very simple source for environment variables for this cause. Do not change `$appName`, as it needs to be the same as the folder name containing the classes. The file path for the csv upload folder, should end at `uploaded/`.
 
## Logging
Whether a file has been validated as correct, or not, it will be logged in syslog. An entry could look like this;

```
App[9676]: File: 2018-01-11-37632.csv | Column: eventDatetime | Line: 6 | Not a valid datetime.
```

The example is from a file going through validation, and is of `LOG_ERR` severity.

**Such a message consists of:**
 - Name of application, `App` (Config `$appName`)
 - PID, `9676`
 - File name, `2018-01-11-37632.csv`
 - Affected column, `eventDatetime`
 - Which line in the file, `6`
 - Message, `Not a valid datetime.`


## About

My hope is that this section will shed some light on why I solved things the way I did, and what I would have done differently.

 - **Lock file:** I was first thinking of making a queue, until I realised that's not what was asked for! :) Instead I decided to go for a very simple solution: When a process is starting, it will create a lock file. When it's done, it will remove this file. The lock file prevents the code from running, if it exists. For a much larger application, I would look into a different solution, especially since such an application may be used across different servers. 

 - **Database setup:** I decided to put this at the very start of the script. The ideal would, however, be to make sure it's only run once; when the script is run the first time, or (better) as a separate task, part of the setup.
 
 - **Failed files:** If a file fails validation, it will create an entry in syslog, and move the file to the `failed` folder. The reason behind moving the file, is to keep the `uploaded` folder clean of wrongly formatted files, that would need to be handled. 
  
 - **Validation:** With `eventCurrencyCode`, I went for a simple solution of checking the length of the string. If done properly, I would likely pull down an external library for this, either stored in a file, or in the database. I would also have kept the rules separate (a Rule class? Something that validates the rules defined for the relevant input).
 
 - **OOP task script:** It's quite the machinery though, so for the purpose of this test, I decided against it.
 
 - **Config:** Ideally, it shouldn't contain any methods.
  

