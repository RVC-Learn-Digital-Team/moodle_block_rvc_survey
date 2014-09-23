<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nigel.Daley
 * Date: 7/28/14
 * Time: 12:43 PM
 * To change this template use File | Settings | File Templates.
 */


defined('MOODLE_INTERNAL') || die;
global $CFG;


//$settings = new admin_settingpage('block_rvc_survey', get_string('pluginname','block_rvc_survey'));

$external_settings 	= new admin_setting_heading('rvc_survey/external_connection', get_string('external_connection', 'block_rvc_survey'), '');
$settings->add($external_settings);
$options = array(
    ' '     => get_string('noconnection','block_rvc_survey'),
    'mssql' => 'Mssql',
    'mysql' => 'Mysql',
    'odbc' => 'Odbc',
    'oci8' => 'Oracle',
    'postgres' => 'Postgres',
    'sybase' => 'Sybase'
);

$external_connection			= 	new admin_setting_configselect('rvc_survey/dbconnectiontype',get_string('dbconnectiontype','block_rvc_survey'),get_string('dbconnectiontype','block_rvc_survey'), '', $options);
$settings->add( $external_connection );

$dbname			=	new admin_setting_configtext('rvc_survey/dbname',get_string( 'db_name', 'block_rvc_survey' ),get_string( 'db_name', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add($dbname);

$dbprefix			=	new admin_setting_configtext('rvc_survey/dbprefix',get_string( 'db_prefix', 'block_rvc_survey' ),get_string( 'db_prefix', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add($dbprefix);

$dbhost			=	new admin_setting_configtext('rvc_survey/dbhost',get_string( 'db_host', 'block_rvc_survey' ), get_string( 'db_host', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add($dbhost);

$dbuser			=	new admin_setting_configtext('rvc_survey/dbuser',get_string( 'db_user', 'block_rvc_survey' ), get_string( 'db_user', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add( $dbuser );

$dbpass			=	new admin_setting_configtext('rvc_survey/dbpass',get_string( 'db_pass', 'block_rvc_survey' ), get_string( 'db_pass', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add($dbpass);

$field			=	new admin_setting_configtext('rvc_survey/table',get_string( 'db_table', 'block_rvc_survey' ), get_string( 'db_table', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add($field);

$field			=	new admin_setting_configtext('rvc_survey/surveyid',get_string( 'surveyid', 'block_rvc_survey' ), get_string( 'surveyid', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add($field);

$field			=	new admin_setting_configtext('rvc_survey/surveytitle',get_string( 'surveytitle', 'block_rvc_survey' ), get_string( 'surveytitle', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add($field);

$field			=	new admin_setting_configtext('rvc_survey/surveyuser',get_string( 'surveyuser', 'block_rvc_survey' ), get_string( 'surveyuser', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add($field);

$field			=	new admin_setting_configtext('rvc_survey/surveyclose',get_string( 'surveyclose', 'block_rvc_survey' ), get_string( 'surveyclose', 'block_rvc_survey' ),'',PARAM_RAW);
$settings->add($field);

