<?php

return [

    /**
     * Register processors here
     */
    'processors' => [
        // add your processor classes here
    ],

    /**
     * Default processor options (these can be overridden per processor)
     */
    'processor_options' => [
        'auto_approve' => false, //mark batch as approved if all rows are valid
        'reject_invalid' => true, //mark batch as rejected if all rows not valid (otherwise pending)
        'csv' => [
            'length' => null,
            'separator' => ",",
            'enclosure' => "\"",
            'escape' => "\\",
            'header_row' => true
        ],
        'dispatch' => [
            'sync' => false,
            'queue' => null, // null will use default queue
            'connection' => null // null will use default connection
        ]
    ],

    /**
     * Register file readers
     */
    'file_readers' => [
        Dandysi\Laravel\BatchUpload\FileReaders\CsvFileReader::class
    ],    
];