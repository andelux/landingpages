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
        $data = array();

        if ( $this->getConfig('add_timestamp') ) {
            $data['timestamp'] = date('Y-m-d H:i:s');
        }

        // MAP first_name=>NAME
        //  ---> CSV[first_name] = POST[NAME]
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
            // Check if it has the same fields (header)
            $f = fopen($file_path,'r');
            $current_fields = fgetcsv($f);
            fclose($f);
            if ( $current_fields != array_keys($data) ) {
                rename($file_path, $file_path.'-'.date('YmdHis'));

                // If file does not exist, then create...
                $f = fopen($file_path, 'w');
                // ...and save the field names as its first row
                fputcsv($f, array_keys($data));
            }

            // If file exists, then open as append...
            $f = fopen($file_path, 'a');
        }

        // Save the row
        fputcsv($f, array_values($data));

        fclose($f);
    }

}
