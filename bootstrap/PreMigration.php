<?php defined('BASEPATH') or exit('No direct script access allowed');

class PreMigration
{
    public function handle()
    {
        $CI = &get_instance();

        // example
        // $CI->db->query("GRANT SELECT ON ALL TABLES IN SCHEMA public TO myrole");

        $CI->load->library('Logger');
        $CI->logger->setLogPath('queries');
        $CI->logger->write_log('debug','User run migration.');
    }
}
