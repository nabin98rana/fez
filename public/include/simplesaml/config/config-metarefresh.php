<?php

$config = array(

        'sets' => array(


                'aaf-metadata' => array(
                        'cron'          => array('hourly'),
                        'sources'       => array(
                                array(
                                        'src' => 'https://manager.test.aaf.edu.au/metadata/metadata.aaf.signed.xml'
//                                        'validateFingerprint' => '55:BB:92:B8:32:94:41:C6:F5:C4:8D:B0:E7:2E:A3:08:1A:AA:F1:CA',
                                ),
                        ),
                        'expireAfter'           => 60*60*24*4, // Maximum 4 days cache time.
                        'outputDir'     => 'metadata/metadata-aaf-consuming/',

                        /*
                         * Which output format the metadata should be saved as.
                         * Can be 'flatfile' or 'serialize'. 'flatfile' is the default.
                         */
                        'outputFormat' => 'flatfile',
                ),


        ),
);

