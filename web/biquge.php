<?php
require __DIR__ .'/../phpfetcher.php';
require __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;
$config = Yaml::parse( file_get_contents( __DIR__ . '/../config/config.yml' ) );
// msyql connect
$database = new \Medoo\Medoo( $config[ 'parameters' ] );



// 笔趣阁书架
class mycrawler extends Phpfetcher_Crawler_Default {
    private $db;
    public function setDb( $db ){
        $this->db = $db;
    }

    private $config;
    public function setConfig( $config ){
        $this->config = $config;
    }

    // 推送消息
    public function sendMessage($config , $message){
        $data = [
            "text"=> $message,
            ];
        $data_string = json_encode($data);
        $ch = curl_init($config['parameters']['telegram']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
                                                                                                                            
        $result = curl_exec($ch);
    }

    public function handlePage($page) {
        $webSite = 'http://m.biquge.com.tw';
        // 数据库中小说对应的那条id
        // $dbId = $config['db_id'];
        $books = $this->db->select('books', ['id','name','biquge']);
        foreach($books as $book){
            // 分别查找
            $res = $page->sel($book['biquge']);
            $novalName = $book['name'];
            foreach($res as $k => $v){
                $bookId = $book['id'];
                var_dump($v->plaintext);
                var_dump($v->href);
                $chapter = $this->db->get('chapter', ['id','title','href'], ['title'=>"{$v->plaintext}" , 'bookId'=>$bookId]);
                var_dump($chapter);
                $href = $webSite . $v->href;
                if(!$chapter){
                    // 没有就..插入并发送消息?
                    $last_chapter_id = $this->db->insert('chapter', [
                                        "title" => $v->plaintext,
                                        "href"  => $href,
                                        "bookId" => $bookId,
                                    ]);
                    // 发送提醒消息
                    $this->sendMessage($this->config , "{$novalName}:\n{$v->plaintext}\n{$href}");
                }else{
                    // 已有则不管了
                }
            }
        }


    }
}

$crawler = new mycrawler();
$crawler->setDb($database);
$crawler->setConfig($config);
$arrJobs = array(
    //放开那个女巫
    'fangkai' => array( 
        'start_page' => 'http://m.biquge.com.tw/wap/bookcase.php', //起始网页,书架
        'link_rules' => array(
        ),
        //爬虫从开始页面算起，最多爬取的深度，设置为1表示只爬取起始页面
        //Crawler's max following depth, 1 stands for only crawl the start page
        'max_depth' => 1,
        'page_conf' => array(
            'http_header' => array(
                'Host:m.biquge.com.tw',
                'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
                'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Connection: keep-alive',
                'Cache-Control: max-age=0',
                'Cookie:__cfduid=def57eecc79a993084913ecdfb39872581490236035; a3777_times=2; a4191_times=2; jieqiUserInfo=jieqiUserId%3D60653%2CjieqiUserUname%3Dwizarot%2CjieqiUserName%3Dwizarot%2CjieqiUserGroup%3D3%2CjieqiUserGroupName%3D%C6%D5%CD%A8%BB%E1%D4%B1%2CjieqiUserVip%3D0%2CjieqiUserHonorId%3D%2CjieqiUserHonor%3D%D0%C2%CA%D6%C9%CF%C2%B7%2CjieqiUserPassword%3Dfcea920f7412b5da7be0cf42b8c93759%2CjieqiUserUname_un%3Dwizarot%2CjieqiUserName_un%3Dwizarot%2CjieqiUserHonor_un%3D%26%23x65B0%3B%26%23x624B%3B%26%23x4E0A%3B%26%23x8DEF%3B%2CjieqiUserGroupName_un%3D%26%23x666E%3B%26%23x901A%3B%26%23x4F1A%3B%26%23x5458%3B%2CjieqiUserLogin%3D1491821658; jieqiVisitInfo=jieqiUserLogin%3D1491821658%2CjieqiUserId%3D60653; PHPSESSID=2dopaoi9k2u6jrjglbt5a542e7; a4188_times=6; a4288_pages=2; a4288_times=3',
            ),
        ),
        
    ) ,   
);

$crawler->setFetchJobs($arrJobs)->run(); //執行一下
