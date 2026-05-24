<?php defined('BASEPATH') or exit('No direct script access allowed');

class PostMigration
{
    public function handle()
    {
        $CI = &get_instance();

        // example
        // $CI->db->query("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO myrole");
        // $CI->db->query("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO myrole");

        $CI->load->library('Logger');
        $CI->logger->setLogPath('queries');
        $CI->logger->write_log('debug','Migrate success.');
    }
}
