<?php

require ('phpQuery.php');

class Go
{
    protected $newSite;
    protected $status = true;
    protected $years;

    public function __construct()
    {
        $this->years = $_GET['a'];
        #совпадение год
        $w = $this->querySql('status');
        while ($row = $w ->fetch_array()){
            if ($row[0] == $this->years){
                $this->status = false;
            }
        }
        if ($this->status) {
            $newSite = file_get_contents("https://www.kinopoisk.ru/lists/m_act[year]/{$this->years}");
            $newSite = phpQuery::newDocumentHTML($newSite, $charset = 'utf-8');
            $this->newSite = $newSite->find('div.item._NO_HIGHLIGHT_');
        }
    }



    protected function getContent($row)
    {
        echo "<div><img src=\"{$row['img']}\">
               {$row['name']}  
               <a href=\"{$row['url']}\">ССЫЛКА</a> 
               {$row['reit']}</div><br> ";
    }

    public function go ()
    {
        $sort = null;
        $i = 0;
        if ($this->status){
            $this->querySql('create');
            foreach ($this->newSite as $val) {
                $name = $this->pars($val, 'div.name > a', ['text', 'attr'], 'href');
                $reit = $this->pars($val, 'div.numVote > span', ['text']);
                $reit[0] = sprintf("%.5s", $reit[0]);
                $img = $this->pars($val, 'div.poster > a > img', ['attr'], 'title');
                $sort[] = ['name' => $name[0],
                    'url' => ('https://www.kinopoisk.ru' . $name[1]),
                    'reit' => $reit[0],
                    'img' =>('https://st.kp.yandex.net' . $img[0])
                ];
                $this->querySql('write', $sort[$i]);
                $this->getContent($sort[$i]);
                ++$i;
            }
        }
        else{
            $row = $this->querySql('select');
            while ($re = $row->fetch_array(MYSQLI_NUM)){
                $sort[] = ['name' => $re[0],
                    'url' => $re[1],
                    'reit' => $re[2],
                    'img' => $re[3]
                ];
                $this->getContent($sort[$i]);
                ++$i;
            }
        }
        ## вывод в виде масива данных
        #return $sort;
    }

    #######  парсинг данных
    protected function pars ($val, $select, $fun, $attr = null)
    {
        $call = null;
        $val = pq($val)->find("{$select}");
        foreach ($fun as $item){
            switch ($item) {
                case 'text':
                    $call[] = $val->text();
                    break;
                case 'attr':
                    $call[] = $val->attr($attr);
                    break;
            }
        }
        return $call;
    }

    protected function querySql ($do, $value = null)
    {
        $back = null;
        $mysqli = $this->connectSql();
        switch ($do){
            case 'create':
                $mysqli->query("CREATE TABLE  `{$this->years}` (name VARCHAR(100), url VARCHAR(100), reit VARCHAR(20), img VARCHAR(100))");
                break;
            case 'write':
                $mysqli->query("INSERT INTO `{$this->years}` VALUES ('{$value['name']}', '{$value['url']}', '{$value['reit']}', '{$value['img']}')");
                break;
            case 'select':
                $back= $mysqli->query("SELECT * FROM `{$this->years}`");
                break;
            case 'status':
                $back = $mysqli->query('SHOW TABLES');
                break;
        }
        return $back;

    }
    protected function connectSql ()
    {
        return new mysqli('localhost', 'root', '123', 'pars');
    }

}
$kino = new Go();
$kino->go();









