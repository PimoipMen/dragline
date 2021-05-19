<?php

/*
 * 基类
 * @author:jacky Version 1.0.0 2016-9-18
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

/**
 * 所有API的基类
 */
class Apibase extends REST_Controller {

    public $model;
    public $server = NULL;

    function __construct()
    {
        parent::__construct();
        $rest_auth = strtolower($this->config->item('rest_auth')); //获取验证配置
        switch ($rest_auth) {
            case 'oauth2':
                //$this->_prepare_oauth2_auth();
                break;
        }
        //$this->check_user_id();
    }

    public function get_version_get()
    {
        $version = $this->config->item('version');
        $this->returnData(['version' => $version]);
    }

    /**
     * 校验用户有效性
     */
    public function check_user_id()
    {
        $request_data = $this->check_param([
            'uid' => ['用户ID', 'integer'],
                ], [], 'get_post');
        if (!empty($request_data['uid'])) {
            $this->load->model('um/um_user');
            $res = $this->um_user->get_one('uid', ['uid' => $request_data['uid']]);
            if (!$res) {
                $this->returnError('用户不存在,请检查');
            }
        }
    }

    /**
     * 限制域名+ip白名单
     */
    protected function early_checks()
    {
        /**
         * 1.获取h5请求api的内网ip地址
         * 2.获取访问的REMOTE_ADDR
         * 3.判断是否是内网访问api
         * 4.如果是内网访问api就将rest_auth 设置为basic auth
         */
        $allow_ips = $this->config->item('allow_ips');
        $allow_domain = $this->config->item('allow_domain');
        $access_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $access_domain = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if (in_array($access_ip, $allow_ips) || in_array($access_domain, $allow_domain) || 'production' != ENVIRONMENT) {
            //$this->config->set_item('rest_auth', 'basic');
        }
    }

    /**
     * 具体执行oauth2验证的动作
     *
     * @access protected
     * @return void
     */
    protected function _prepare_oauth2_auth()
    {
        $this->server();
        if ('token' == $this->uri->segment(3)) {//获取token不走这个验证            
            return TRUE;
        }

        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
            $response = new OAuth2\Response();
            exit;
        }
    }

    /**
     * 构造Ouath2 server
     */
    private function server()
    {
        $_POST['client_id'] = isset($_POST['app_id']) ? $_POST['app_id'] : '';
        $_POST['client_secret'] = isset($_POST['app_secret']) ? $_POST['app_secret'] : '';
        $oauth2_tables_config = $this->config->item('oauth2_tables'); //获取配置表        
        $connection = ['dsn' => $this->db->dsn, 'username' => $this->db->username, 'password' => $this->db->password]; //获取数据库连接信息
        OAuth2\Autoloader::register();
        //正式环境启用redis作为验证的存储
        if ('production' === ENVIRONMENT) {
            $this->load->driver('cache');
            $redis = $this->cache->redis->get_normal_redis();
            $storage = new OAuth2\Storage\Redis($redis);
        } else {
            $storage = new OAuth2\Storage\Pdo($connection, $oauth2_tables_config);
        }
        // 初始化server
        $this->server = new OAuth2\Server($storage);

        // 添加 "Client Credentials" 授权类型 (最简单的授权类型)
        $this->server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
        // 添加 "RefreshToken" 授权类型 (最简单的授权类型)
        $this->server->addGrantType(new OAuth2\GrantType\RefreshToken($storage));
        // 添加 "Authorization Code" 授权类型 (this is where the oauth magic happens)
        $this->server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
    }

    /**
     * 校验用户有效性
     * db grant_type: client_credentials
     */
    public function token_post()
    {
        $this->server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
    }

    /**
     * http://my-huitianxia.com/welcome/authorize?response_type=code&client_id=testclient&state=xyz
     * db grant_type: authorization_code
     */
    public function authorize_post()
    {
        $request = OAuth2\Request::createFromGlobals();
        $response = new OAuth2\Response();

        // validate the authorize request
        if (!$this->server->validateAuthorizeRequest($request, $response)) {
            $response->send();
            die;
        }

        // print the authorization code if the user has authorized your client
        $is_authorized = ($_POST['authorized'] === 'yes');

        $this->server->handleAuthorizeRequest($request, $response, $is_authorized);
        if ($is_authorized) {
            // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
            $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
            exit("SUCCESS! Authorization Code: $code");
        }
        $response->send();
    }

    /**
     * 统一API参数检验方法
     * 调用示例 check_param(array('money' => array('required', 'integer', 'greater_than_equal_to[1]', 'less_than_equal_to[200]')));
     * @param   array       $arr
     * @return  boolean
     */
    public function check_param($arr, $data = array(), $method = 'get')
    {
        /**
         * 设置要验证的请求数据
         */
        if (!empty($arr)) {
            $key_arr = array();
            $rule_arr = array();
            $tmp_arr = [];
            foreach ($arr as $key => $value) {
                $tmp_arr[$key] = array_shift($value);
                $temp_arr = explode(",", $key);
                if (!is_array($value)) {
                    $value = explode("|", $value);
                }
                $key_arr = array_merge($key_arr, $temp_arr);
                if (!empty($temp_arr)) {
                    foreach ($temp_arr as $temp_value) {
                        if (!empty($rule_arr[$temp_value])) {
                            $rule_arr[$temp_value] = array_merge($rule_arr[$temp_value], $value);
                        } else {
                            $rule_arr[$temp_value] = $value;
                        }
                    }
                }
            }
            $key_arr = array_unique($key_arr);
            if (!empty($rule_arr)) {
                foreach ($rule_arr as $rule_key => $rule_value) {
                    $rule_arr[$rule_key] = array_unique($rule_value);
                }
            }
        }
        if ($method === 'post' || $method === 'POST') {
            $request_data = $this->input->post($key_arr, TRUE);
        } else {
            $request_data = $this->input->get($key_arr, TRUE);
        }

        if ('get_post' == $method) {
            $request_data = [];
            foreach ($key_arr as $one_key) {
                $request_data[$one_key] = $this->input->get_post($one_key, TRUE);
            }
        }

        $this->form_validation->set_data($request_data);
        /**
         * 设置验证规则
         */
        if (!empty($rule_arr)) {
            foreach ($rule_arr as $rule_key => $rule_value) {
                $this->form_validation->set_rules($rule_key, '', $rule_value, array('required' => '%s 不能为空;'
                    , 'integer' => '%s 必须是数字;'
                    , 'regex_match' => '%s 格式有误;'
                    , 'greater_than' => '%s 有误;'
                    , 'max_length' => '%s 超过长度;'
                    , 'min_length' => '%s 长度不够;'
                ));
            }
        }
        /**
         * 开始验证
         */
        if (!$this->form_validation->run()) {
            //验证失败处理逻辑
            $errmsg = validation_errors(' ', ' ');
            if (!empty($tmp_arr)) {
                foreach ($tmp_arr as $arr_key => $arr_value) {
                    if ($arr_value) {
                        $errmsg = str_replace($arr_key, $tmp_arr[$arr_key], $errmsg);
                    }
                }
            }
            $this->returnError($errmsg . "请检查是否正确", '400');
            return FALSE;
        }
        return $request_data;
    }

    /**
     * api返回数据格式
     * @param type $data
     * @param type $msg
     * @param type $code
     */
    public function returnData($data = [], $msg = '', $code = self::HTTP_OK)
    {
        $ret_data = array(
            'code' => $code . '',
            'msg' => $msg,
            'data' => $data,
        );
        $http_code = $code; //新的api让code跟http_code保持一致
        set_data_string($ret_data);
        $this->response($ret_data, $http_code, TRUE);
    }

    public function returnDataCache($data, $cache_time = 60, $msg = '', $code = self::HTTP_OK)
    {
        $method = $this->input->method();
        $key = md5(uri_string() . current_url() . implode('', $this->input->$method()));
        $this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        if ('testing' === ENVIRONMENT) {
            $this->load->driver('cache', array('adapter' => 'redis', 'backup' => 'file'));
        }
        $cache_data = [
            'data' => $data,
            'msg' => $msg,
            'code' => $code,
            'return_fun' => 'returnData',
        ];
        $this->cache->save($key, serialize($cache_data), $cache_time);
        $this->returnData($data, $msg, $code);
    }

    /**
     * 获取数据失败提示2(上一个方法提供数据给Android有时候会返回异常数据)
     * @param type $msg
     * @param type $code
     */
    public function returnError($msg = '', $code = self::HTTP_BAD_REQUEST)
    {
        echo json_encode(['code' => $code . '', 'msg' => $msg, 'data' => []]);
        exit;
    }

    /**
     * 移动端分页列表数据接口 ,供子类参照或复写，不建议API直接调用此方法
     * 调用可参照Api_software/softwares_get方法
     */
    public function list_get()
    {
        /**
         * 获取要查询的SQL，复杂SQL通过复写或参照模型层getListSql方法
         */
        $sql = $this->model->getListSql("*", NULL);
        /**
         * 根据$sql, $order获取分页数据
         */
        $grid = $this->grid($sql, NULL);
        $this->returnData($grid);
    }

    /**
     * 获取分页列表数据接口，不提供子类复写,子类可调用
     * 注意：此处不需要filter
     */
    protected function grid($sql = NULL, $order = NULL)
    {
        if (empty($sql)) {
            $sql = $this->model->getListSql();
        }
        /**
         * 校验参数是否合法
         */
        $request_data = $this->check_param([
            "page" => ["integer", "greater_than_equal_to[1]"],
            "page_size" => ["integer", "greater_than_equal_to[1]", "less_than_equal_to[1000]"],
                ], [], $_SERVER['REQUEST_METHOD']);
        /**
         * 根据$sql, $order，查询出分页列表数据
         */
        $grid = $this->model->list_do($sql, $order, $request_data['page'], $request_data['page_size'], FALSE);
        return $grid;
    }

    /* ----------------------------------------------------- 后台管理系统常用方法----------------------------------------------------- */

    /**
     * f7字段数据查询，直接供js api调用
     * @return type
     */
    public function f7_get()
    {
        $this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        $select = $this->input->get_post('select');
        $filter = $this->input->get_post('filter');
        $order = $this->input->get_post('order');
        if (empty($select)) {
            $select = "*";
        }
        $cache_key = __FUNCTION__ . '_' . $this->model->_table . $select . $filter . $order;
        $cache_data = $this->cache->get($cache_key);
        if ($cache_data) {
            $cache_data = json_decode($cache_data, TRUE);
            $this->returnData($cache_data);
            return;
        }
        $f7 = $this->model->f7($select, $filter, $order);
        $this->cache->save($cache_key, json_encode($f7), 30);
        $this->returnData($f7);
    }

    /**
     * 列表数据查询，直接供js api调用
     */
    public function em_grid_post()
    {
        $select = $this->input->get_post('select');
        $page_size = $this->input->get_post('rows');
        $page = $this->input->get_post('page');
        $sidx = $this->input->get_post('sidx');
        $sord = $this->input->get_post('sord');
        $filter = $this->input->get_post('filter');
        $order = $this->input->get_post('order');
        $grid = $this->model->em_list($select, $filter, $page, $page_size, $sidx, $sord, $order);
        $this->response($grid, self::HTTP_OK);
    }

    /**
     * 列表数据查询，直接供js api调用
     */
    public function grid_post()
    {
        $select = $this->input->get_post('select');
        $page_size = $this->input->get_post('rows');
        $page = $this->input->get_post('page');
        $sidx = $this->input->get_post('sidx');
        $sord = $this->input->get_post('sord');
        $filter = $this->input->get_post('filter');
        $order = $this->input->get_post('order');
        //writelog(["select" => $select]);
        $grid = $this->model->grid($select, $filter, $page, $page_size, $sidx, $sord, $order);
        $this->response($grid, self::HTTP_OK);
    }

    /**
     * 移动端分页列表数据接口
     * @param type $sql
     * @return type
     */
    public function list_post($sql)
    {
        $request_data = $this->check_param(array("page" => array("integer", "greater_than_equal_to[1]"),
            "rows" => array("integer", "greater_than_equal_to[1]", "less_than_equal_to[1000]"),
            "filter" => array("max_length[100]"),
            "order" => array("max_length[100]"),
                ), [], $_SERVER['REQUEST_METHOD']);
        $request_data['filter'] = htmlspecialchars_decode($request_data['filter']);
        $request_data['order'] = htmlspecialchars_decode($request_data['order']);
        $grid = $this->model->list_do($sql, $request_data['filter'], $request_data['order'], $request_data['page'], $request_data['rows'], TRUE);
        return $grid;
    }

    /**
     * 获取列表数据
     * @param type $postData
     * @param type $isCount
     * @return type
     */
    public function getGrid($postData, $isCount, $flag_read_slave = false)
    {
        $select = empty($postData['select']) ? NULL : $postData['select'];
        $page_size = empty($postData['rows']) ? 10000 : $postData['rows'];
        $page = empty($postData['page']) ? 1 : $postData['page'];
        $sidx = empty($postData['sidx']) ? NULL : $postData['sidx'];
        $sord = empty($postData['sord']) ? NULL : $postData['sord'];
        $filter = empty($postData['filter']) ? NULL : $postData['filter'];
        $order = empty($postData['order']) ? NULL : $postData['order'];
        //writelog(['msg' => 'cexo', '$flag_read_slave' => $flag_read_slave]);
        $grid = $this->model->grid($select, $filter, $page, $page_size, $sidx, $sord, $order, $isCount, $flag_read_slave);
        return $grid;
    }

    /**
     * 设置导出的excel数据
     * @param type $objPHPExcel
     * @param type $colModel
     * @param type $grid
     */
    public function setExcelData($objPHPExcel, $colModel, $grid)
    {
        $table = $this->model->_table;
        for ($i = 0, $t = 0; $i < count($colModel); $i++) {
            $label = $colModel[$i]['label'];
            $name = $colModel[$i]['name'];
            $hidden = empty($colModel[$i]['hidden']) ? FALSE : $colModel[$i]['hidden'];
            $key = empty($colModel[$i]['key']) ? FALSE : $colModel[$i]['key'];
            $sorttype = empty($colModel[$i]['sorttype']) ? 'string' : $colModel[$i]['sorttype'];
            $width = empty($colModel[$i]['width']) ? 20 : $colModel[$i]['width'] / 8;
            $isEdit = empty($colModel[$i]['editable']) ? FALSE : $colModel[$i]['editable'];
            if ($isEdit && !empty($name) && strstr($name, $table . ".") == $name) {
                $isEdit = TRUE;
            } else {
                $isEdit = FALSE;
            }
            if ($hidden && !$key) {
                continue;
            }
            if ($key) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($t, 1, $label)->getStyleByColumnAndRow($t, 1)->getFont()->setBold(true)->getColor()->setRGB('FF0000');
            } else if ($isEdit) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($t, 1, $label)->getStyleByColumnAndRow($t, 1)->getFont()->setBold(true)->getColor()->setRGB('00FF00');
            } else {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($t, 1, $label)->getStyleByColumnAndRow($t, 1)->getFont()->setBold(true);
            }
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($t)->setWidth($width);
            for ($j = 0; $j < count($grid["rows"]); $j++) {
                $item = $grid["rows"][$j];
                $value = $item[$name];
                if ($sorttype === 'image' && !empty($value)) {
                    $url = $this->saveImage("statics/uploads/export_excel_images/", $value);
                    if (!$url || !isImage($url)) {
                        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow($t, $j + 2, $value, PHPExcel_Cell_DataType::TYPE_STRING);
                        continue;
                    }
                    $img = new PHPExcel_Worksheet_Drawing();
                    $img->setName($name);
                    $img->setDescription($name);
                    $img->setPath($url);
                    $img->setWidth(40); //写入图片宽度
                    $img->setHeight(40); //写入图片高度
                    $img->setOffsetX(1); //写入图片在指定格中的X坐标值
                    $img->setOffsetY(1); //写入图片在指定格中的Y坐标值
                    $img->setRotation(1); //设置旋转角度
                    $img->getShadow()->setVisible(true);
                    $img->getShadow()->setDirection(50);
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($t);
                    $coordinate = $columnLetter . ($j + 2);
                    $img->setCoordinates($coordinate); //设置图片所在表格位置
                    $img->setWorksheet($objPHPExcel->getActiveSheet()); //把图片写到当前的表格中
                } else if ($sorttype === 'datetime' && !empty($value)) {
                    if (strtotime($value)) {
                        $value = strtotime($value);
                    }
                    $value = date("Y-m-d H:m:s", $value);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow($t, $j + 2, $value, PHPExcel_Cell_DataType::TYPE_STRING);
                } else if ($sorttype === 'number') {
                    $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow($t, $j + 2, $value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow($t, $j + 2, $value, PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
            $t++;
        }
    }

    /**
     * 导出excel
     */
    public function exportExcel_POST()
    {
        $postData = $this->check_param(array(
            'page' => array('required', 'integer'),
            'keyName' => array('required'),
            'sidx' => array('required'),
            'sord' => array('required'),
            'rows' => array('required', 'integer'),
            'filter' => array('required'),
            'title' => array('required'),
            'select' => array('required'),
            'colModel' => array('required')
                ), null, 'post');
        writelog(['msg' => '上传文件接收参数', 'postData' => $postData]);
        $grid = $this->getGrid($postData, FALSE, TRUE);
        writelog(['msg2' => '上传文件接收参数2', 'grid' => $grid]);
        $title = empty($postData['title']) ? "youliang" : $postData['title'];
        $colModel = empty($postData['colModel']) ? NULL : $postData['colModel'];
        $colModel = json_decode($colModel, TRUE);
        require_once APPPATH . '/third_party/PHPExcel.php';
        require_once APPPATH . '/third_party/PHPExcel/Writer/Excel5.php';
        $fileName = $title . date("YmdHis");
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("有量");
        $objPHPExcel->getProperties()->setLastModifiedBy("1.gaopeng.com");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Document");
        $objPHPExcel->getProperties()->setDescription("有量");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(30);
        //填充数据
        $this->setExcelData($objPHPExcel, $colModel, $grid);
        $objPHPExcel->getActiveSheet()->setTitle($title);
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        //$pub_dir = FCPATH . "statics/uploads/export_excels/";
        $pub_dir = FCPATH . "resource/admin/uploads/export_excels/";
        if (!is_dir($pub_dir)) {
            @mkdir($pub_dir, 0777, TRUE);
        }
        $file_name = $pub_dir . $fileName . ".xls";
        //$file_name = iconv("UTF-8", "gb2312", $file_name);//windows需要转换为gb2312
        $objWriter->save($file_name);

        //存入cos服务器
        /* $this->load->library('txcos');
          $cos_adr = DIRECTORY_SEPARATOR . 'export_excels' . DIRECTORY_SEPARATOR . date("Ymd") . DIRECTORY_SEPARATOR . $fileName . '.xls';
          $cos_adr = str_replace('\\', '/', $cos_adr);
          $rs = $this->txcos->upload($file_name, $cos_adr);
          $cdn_url = isset($rs['data']['access_url']) ? $rs['data']['access_url'] : '';
          if (empty($cdn_url)) {
          writelog(['msg' => '文件上传cos失败,生成失败', 'upload_info' => $rs]);
          $this->returnError('文件生成失败');
          } */
        $file_name = base_url() . "resource/admin/uploads/export_excels/" . $fileName . ".xls";
        $this->returnData($file_name);
    }

    /**
     * 导入excel数据
     */
    public function importExcel_post()
    {
        $table = $this->model->_table;
        $files = $this->input->get_post('files_base64_string');
        $receive_file_name = $this->input->get_post('files_name');
        //writelog(['msg' => '导入', 'dir' => $dir, 'files' => $files, 'receive_file_name' => $receive_file_name]);

        if (!empty($receive_file_name)) {
            $temp_arr = explode(".", $receive_file_name);
            $file_type = end($temp_arr);
            /* 判别是不是.xls文件，判别是不是excel文件 */
            if (strtolower($file_type) != "xls") {
                $this->error('不是Excel文件，重新上传');
            }
            /* 设置上传路径 */
            $savePath = FCPATH . 'statics/uploads/import_files/';
            if (!is_dir($savePath)) {
                @mkdir($savePath, 0777, TRUE);
            }
            /* 以时间来命名上传的文件 */
            $str = date('Ymdhis');
            $file_name = $receive_file_name . "." . $str . "." . $file_type;
            /* 是否上传成功 */
            $file_name = iconv("UTF-8", "gb2312", $file_name);
            $file_path = $savePath . $file_name;
            if (!base64_to_img($files, $file_path)) {
                writelog(["文件上传失败" => $file_path]);
                $this->error('文件上传失败');
            }

            //获取字段配置
            $colModel = $this->input->get_post('colModel');
            $colModel = urldecode($colModel);
            $colModel = json_decode($colModel, TRUE);
            $key = $table . ".id";
            for ($i = 0; $i < count($colModel); $i++) {
                if (isset($colModel[$i]['key']) && $colModel[$i]['key'] == true) {
                    $key = $colModel[$i]['name'];
                    break;
                }
            }
            $excelData = $this->readExcel($file_path);
            foreach ($excelData as $index => $rowData) {
                if ($index >= 2) {
                    $rowInfo = array_combine($excelData[1], $rowData);
                    $check_result = $this->checkRowData($rowInfo, $colModel);
                    if ($check_result) {
                        $modelInfo = array();
                        foreach ($colModel as $col_k => $col_v) {
                            if (($col_v['name'] == $key) || (TRUE == $col_v['editable'] && !empty($col_v['name']) && strstr($col_v['name'], $table . ".") == $col_v['name'] && !empty($rowInfo[$col_v['label']]))) {
                                $value = $rowInfo[$col_v['label']];
                                if ("设置为空值" == $value) {
                                    $value = NULL;
                                }
                                $modelInfo[$col_v['name']] = $value;
                            }
                        }
                        $res = $this->update_or_add_do($modelInfo, $colModel);
                    }
                }
            }
        }
        $redirect = $this->input->get_post('redirect');
        $redirect = urldecode($redirect);
        if (!empty($redirect)) {
            header('Location:' . $redirect);
            //redirect($redirect, 'refresh', 302);
        }
    }

    /**
     * 添加任务
     * @param type $funname
     * @param type $data
     * @return boolean
     */
    public function add_task($funname, $data = array())
    {
        if (!class_exists('GearmanClient')) {
            return -3;
        }
        try {
            $client = new GearmanClient();
//            if (ENVIRONMENT == 'production') {
//                $client->addServer('10.105.105.60');
//            } else {
//                $client->addServer();
//            }
            $server_host = $this->config->item('gearman_host');
            $client->addServer($server_host);

            $client->doBackground($funname, serialize($data));
            if ($client->returnCode() != GEARMAN_SUCCESS) {
                return -1;
            }
            return TRUE;
        } catch (Exception $e) {
            return -2;
        }
    }

    /**
     * 检查导入的数据
     * @param type $rowData
     * @param type $cols
     * @return boolean
     */
    public function checkRowData($rowData, $colModel)
    {
        return TRUE;
    }

    /**
     * 读取EXCEL
     * @param type $filename
     * @param type $encode
     * @return type
     */
    public function readExcel($filename, $encode = 'utf-8')
    {
        $excelData = array();
        require_once APPPATH . 'third_party/PHPExcel/IOFactory.php';
        if (!file_exists($filename)) {
            return $excelData;
        }
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                $value = $cell->getValue();
                if ($cell->getDataType() == PHPExcel_Cell_DataType::TYPE_NUMERIC) {
                    if ($value > 25569 && strstr($value, ".") && strlen(end(explode(".", $value . ''))) > 4) {
                        $value = gmdate("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($value));
                    } else {
                        $value = PHPExcel_Style_NumberFormat::toFormattedString($value, $formatcode);
                    }
                }
                $excelData[$row][] = $value;
            }
        }
        return $excelData;
    }

    /**
     * 任意指定一个网络图片地址，通过这个函数下载到本地服务器
     * @param type $path
     */
    public function saveImage($pub_dir, $path)
    {
        try {
            $pub_dir = FCPATH . $pub_dir;
            if (!is_dir($pub_dir)) {
                @mkdir($pub_dir, 0777, TRUE);
            }
            $image_name = str_replace(":", "B", $path);
            $image_name = str_replace("/", "1", $image_name);
            $image_name = str_replace(".", "_", $image_name) . ".png";
            $file_path = $pub_dir . $image_name;
            if (file_exists($file_path)) {
                return $file_path;
            }
            if (!strstr($path, "//")) {
                $path = "statics/uploads/" . $path;
                copy($path, $file_path);
                return $file_path;
            }
            $ch = curl_init($path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            $img = curl_exec($ch);
            curl_close($ch);
            $fp = fopen($pub_dir . $image_name, 'w');
            fwrite($fp, $img);
            fclose($fp);
            return $file_path;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * 导出word
     */
    public function exportWord()
    {
        $postData = $this->input->get_post('postData');
        $postData = urldecode($postData);
        $postData = json_decode($postData, TRUE);
        $title = empty($postData['title']) ? NULL : $postData['title'];
        $content = empty($postData['content']) ? NULL : $postData['content'];
        $absolutePath = empty($postData['absolutePath']) ? NULL : $postData['absolutePath'];
        $isEraseLink = empty($postData['isEraseLink']) ? TRUE : $postData['isEraseLink'];
        $word = PHPWord::getWordDocument($content, $absolutePath, $isEraseLink);
        if (empty($title)) {
            $title = "ejiuzhen";
        }
        $fileName = $title . date("YmdHis");
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . strlen($word));
        header('Content-Disposition: attachment; filename=' . $fileName . '.doc');
        header("Pragma:no-cache");
        header("Expires:0");
        echo $word;
    }

    /**
     * 导出pdf
     */
    public function exportPDF()
    {
        $this->exportWord();
    }

    /**
     * 统一获取信息，子类不要复写，直接供js api调用
     */
    public function info_post()
    {
        try {
            $select = $this->input->get_post('select');
            $filter = $this->input->get_post('filter');
            $order = $this->input->get_post('order');
            $info = $this->model->info($select, $filter, $order);
            $info = $this->afterInfo($info);
            //writelog(['msg' => "info调试信息", 'info' => $info]);
            $this->returnData($info);
        } catch (Exception $e) {
            $this->returnError($e);
        }
    }

    /**
     * 获取详情后操作，提供给子类复写
     * @param type $info
     * @return type
     */
    public function afterInfo($info)
    {
        return $info;
    }

    /**
     * 统一编辑、新增，子类不要复写，直接供js api调用
     */
    public function update_or_add_post()
    {
        //加载缓存
        $this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        //获取表单信息
        $modelInfo = $this->input->get_post('modelInfo');
        $modelInfo = urldecode($modelInfo);
        $modelInfo = json_decode($modelInfo, TRUE);
        //获取表单配置
        $colModel = $this->input->get_post('colModel');
        $colModel = urldecode($colModel);
        $colModel = json_decode($colModel, TRUE);
        $res = $this->update_or_add_do($modelInfo, $colModel);
        //writelog(['$modelInfo' => $modelInfo, '$colModel' => $colModel]);
        $this->returnData($res);
    }

    /**
     * 统一编辑、新增，子类不要复写
     */
    public function update_or_add_do($modelInfo, $colModel)
    {
        //加载缓存
        $this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        //获取更新主表
        $table = $this->model->_table;
        $key = $table . ".id";
        for ($i = 0; $i < count($colModel); $i++) {
            if (isset($colModel[$i]['key']) && $colModel[$i]['key'] == true) {
                $key = $colModel[$i]['name'];
            }
            if (!empty($colModel[$i]['name']) && !strstr($colModel[$i]['name'], $table . ".")) {
                unset($modelInfo[$colModel[$i]['name']]);
            }
        }
        $id = empty($modelInfo[$key]) ? "" : $modelInfo[$key];
        //提供接口处理表单信息，供子类复写
        $modelInfo = $this->before_update($modelInfo);
        //提供接口处理表单校验，供子类复写
        $is_ok = $this->update_check($modelInfo);
        //校验失败，返回统一错误码
        if ($is_ok !== TRUE) {
            $this->returnError($is_ok);
        }
        if (empty($id)) {
            //新增，处理
            unset($modelInfo[$key]);
            //$modelInfo[$table . '.create_at'] = date("Y-m-d H:i:s");
            $this->db->insert($table, $modelInfo);
            $id = $this->db->insert_id();
            $sql = $this->db->last_query();
        } else {
            //编辑，处理
            $this->db->where($key, $id);
            $this->db->update($table, $modelInfo);
            $num = $this->db->affected_rows();
            $sql = $this->db->last_query();
        }
        $res = array("sql" => $sql, "id" => $id);
        //删除缓存
        $this->cache->delete('f7_get_' . $table);
        $this->cache->delete('Home.index.line_data');
        $modelInfo[$key] = $id;
        $modelInfo = $this->after_update($modelInfo);
        return $res;
    }

    /**
     * 编辑或新增前操作，供子类复写
     * @param type $modelInfo
     * @return type
     */
    public function before_update($modelInfo)
    {
        return $modelInfo;
    }

    /**
     * 编辑或新增后操作，供子类复写
     * @param type $modelInfo
     * @return type
     */
    public function after_update($modelInfo)
    {
        return $modelInfo;
    }

    /**
     * 编辑或新增前数据校验，供子类复写
     * @param type $modelInfo
     * @return boolean
     */
    public function update_check($modelInfo)
    {
        return TRUE;
    }

    /**
     * 删除数据
     */
    public function delete_post()
    {
        $filter = $this->input->get_post('filter', FALSE);
        $table = $this->model->_table;
        $ret = $this->db->delete($table, $filter);
        $this->returnData($ret);
    }

    /**
     * 获取附件，子类不要复写
     */
    public function file_get()
    {
        $relation_table = $this->input->get_post('relation_table');
        $relation_id = $this->input->get_post('relation_id');
        $type = $this->input->get_post('type');
        $this->db->select("*");
        $this->db->from('am_attachment');
        if (!empty($relation_table)) {
            $this->db->where("am_attachment.relation_table", $relation_table);
        }
        if (!empty($relation_id)) {
            $this->db->where("am_attachment.relation_id", $relation_id);
        }
        if (!empty($type)) {
            $this->db->where("am_attachment.type", $type);
        }
        $this->db->order_by("am_attachment.create_at desc");
        $query = $this->db->get();
        $rows = (!$query->num_rows()) ? NULL : $query->result_array();
        $this->returnData($rows);
    }

    /**
     * 获取附件链接，子类不要复写
     * @return boolean
     */
    public function attachment_get()
    {
//        $this->check_admin_id();
        /**
         * 文件名不要用中文
         * $path = "/uploads/20151109/a.doc";
         * echo base64_encode($path);
         * http://william.ejiuzhen.net/api/user/attachment?f=L3VwbG9hZHMvMjAxNTExMDkvYS5kb2M=
         *                                                   Qzovd2FtcC93d3cvZWppdXpoZW4vYXBwbGljYXRpb24vdXBsb2Fkcy8xNTExMTAvOWY1YzM3YWFkODc5ZjkxOTcxYWU0NjkyOGRhMTczMWIucG5n
         */
        $f = $this->input->get('f');
        $f = str_replace(' ', '+', $f);
        $f = base64_decode($f);

        if (!$f) {
            return FALSE;
        }
        $PE_imgpath = APPPATH . $f;
        if (!file_exists($PE_imgpath)) {
            return FALSE;
        }
        $PE_imgarray = pathinfo($PE_imgpath);
        $PE_imgarray["extension"] = strtolower($PE_imgarray["extension"]);
        switch ($PE_imgarray["extension"]) {
            case 'jpeg':
            case 'jpg':
            case 'png':
            case 'gif':
                $iconcontent = file_get_contents($PE_imgpath);
                header("Content-type: image/" . $PE_imgarray["extension"]);
                header('Content-length: ' . strlen($iconcontent));
                echo $iconcontent;
                break;
            case 'doc';
            case 'docx';
            case 'pdf';
                $this->downloadFile($PE_imgpath);
            default:
                break;
        }
    }

    /**
     * 下载文件
     * @param type $fullPath
     */
    function downloadFile($fullPath)
    {
        // Must be fresh start
        if (headers_sent()) {
            die('Headers Sent');
        }
        // Required for some browsers
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }
        // File Exists?
        if (file_exists($fullPath)) {
            // Parse Info / Get Extension
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            $ext = strtolower($path_parts["extension"]);
            // Determine Content Type
            switch ($ext) {
                case "pdf": $ctype = "application/pdf";
                    break;
                case "exe": $ctype = "application/octet-stream";
                    break;
                case "zip": $ctype = "application/zip";
                    break;
                case "doc": $ctype = "application/msword";
                    break;
                case "xls": $ctype = "application/vnd.ms-excel";
                    break;
                case "ppt": $ctype = "application/vnd.ms-powerpoint";
                    break;
                case "gif": $ctype = "image/gif";
                    break;
                case "png": $ctype = "image/png";
                    break;
                case "jpeg":
                case "jpg": $ctype = "image/jpg";
                    break;
                default: $ctype = "application/force-download";
            }
            $full_name = $path_parts['filename'] . '.' . $path_parts['extension'];
            header("Content-type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . $fsize);
            header('Content-Disposition: attachment; filename=' . $full_name);
            header("Pragma:no-cache");
            header("Expires:0");
            ob_clean();
            flush();
            readfile($fullPath);
        } else
            die('File Not Found');
    }

    public function attachment_post()
    {
        $dir = $this->input->get_post('dir');
        $files = $this->input->get_post('files_base64_string');
        $receive_file_name = $this->input->get_post('files_name');
        //writelog(array("attachment_______________dir" => $dir, "files" => $files));
        if (!empty($receive_file_name)) {
            $file_ext = strrchr($receive_file_name, '.');
            $temp_arr = strstr($receive_file_name, $file_ext, true);
            $cur_path = $dir . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
            //$savePath = 'statics' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $cur_path;
            $savePath = 'resource' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $cur_path;
            if (!is_dir($savePath)) {
                $mkres = @mkdir($savePath, 0777, TRUE);
                writelog(array("attachment__________mkres" => $mkres, "savePath" => $savePath));
            }
            $file_name = $this->make_rand_code() . $file_ext;
            $file_path = FCPATH . $savePath . $file_name;
            if (!$this->base64_to_img($files, $file_path)) {
                writelog(["上传失败" => $file_path]);
            }
            $http_url = base_url() . $savePath . $file_name;
            $http_url = str_replace("\\", "/", $http_url);
            $is_image = isImage($file_path);
            $res = array("url" => $http_url, "real_path" => $file_path, "is_image" => $is_image, "file_ext" => $file_ext, "files" => '');
            $this->returnData($res);
        }
    }

}
