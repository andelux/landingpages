<?php
namespace LandingPages\Hook;

use LandingPages\Hook;

class Csv extends Backend
{
    /**
     *
     * @config map: ArrayAssoc [csv_field_name] => post_var_name
     * @config file_path: CSV file name
     *
     */
    public function exec()
    {
        // MAP first_name=>NAME
        //  ---> CSV[first_name] = POST[NAME]
        $data = array();
        foreach ( $this->getConfig('map', array()) as $csv_field => $post_field) {
            $data[$csv_field] = $this->getData($post_field);
        }

        // CSV file name
        $file_path = $this->getConfig('file_path', 'leads.csv');

        if ( ! is_file($file_path) ) {
            // If file does not exist, then create...
            $f = fopen($file_path, 'w');
            // ...and save the field names as its first row
            fputcsv($f, array_keys($data));
        } else {
            // If file exists, then open as append...
            $f = fopen($file_path, 'a');
        }

        // Save the row
        fputcsv($f, array_values($data));

        fclose($f);
    }

}
