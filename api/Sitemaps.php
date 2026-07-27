<?php

require_once('Okay.php');

class Sitemaps extends Okay
{


    private $fileXml = '';
    private $main_url;
    const MAX_URLS = 10000;
    /**
     * @var int
     */
    private $page = 1;

    public function __construct()
    {
        $language = $this->languages->get_language($this->languages->lang_id());
        $params['l'] = '';
        $lang_link = '';
        if (!empty($language)) {
            $params['l'] = '_' . $language->label;
            $lang_link = $this->languages->get_lang_link();
        }
        $this->main_url = $this->config->root_url . '/' . $lang_link;

    }

    public function getSitemapItem()
    {
        $this->setHeader(1);
        $q = $this->request->get('q', 'string');

        list($entity, $params) = explode('_', $q);

        $this->setPage($params);

        switch ($entity) {
            case 'blogs':
                $this->setBlogs(1);
                break;
            case 'categories':
                $this->setCategories(1);
                break;
            case 'brands':
                $this->setBrands(1);
                break;
            case 'products':
                $this->setProducts(1);
                break;
            case 'categories-features':
                $this->setCategoriesFeatures(1);
                break;
            case 'categories-brands':
                $this->setCategoriesBrands(1);
                break;
            default:
                $this->setPages(1);
        }
        $this->setFooter(1);
    }

    public function getSitemapMenu()
    {

        $this->setHeader();
        $this->setMenu();
        $this->setFooter();
    }

    private function setHeader($showLinks = false)
    {
        if ($showLinks) {
            $this->fileXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        } else {
            $this->fileXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        }
    }

    private function setFooter($showLinks = false)
    {
        if ($showLinks) {
            $this->fileXml .= '</urlset>';
        } else {
            $this->fileXml .= '</sitemapindex>';
        }
    }

    public function getXML()
    {
        return $this->fileXml;
    }

    private function setMenu()
    {
        $this->setPages();
        $this->setBlogs();
        $this->setCategories();
        $this->setBrands();
        $this->setProducts();
        $this->setCategoriesFeatures();
        $this->setCategoriesBrands();

    }

    public function writeMenuItem(array $params)
    {
        if (!empty($params)) {
            $str = "\t<sitemap>\n";
            if (!empty($params['url'])) {
                $str .= "\t\t<loc>{$params['url']}</loc>\n";
            }
            if (!empty($params['lastmod'])) {
                $str .= "\t\t<lastmod>{$params['lastmod']}</lastmod>\n";
            }
            $str .= "\t</sitemap>\n";

            $this->fileXml .= $str;
        }
    }

    public function writeItem(array $params)
    {
        if (!empty($params)) {
            $str = "\t<url>\n";
            if (!empty($params['url'])) {
                $str .= "\t\t<loc>{$params['url']}</loc>\n";
            }
            if (!empty($params['lastmod'])) {
                $str .= "\t\t<lastmod>{$params['lastmod']}</lastmod>\n";
            }
            if (!empty($params['changefreq'])) {
                $str .= "\t\t<changefreq>{$params['changefreq']}</changefreq>\n";
            }
            if (!empty($params['priority'])) {
                $str .= "\t\t<priority>{$params['priority']}</priority>\n";
            }
            $str .= "\t</url>\n";

            $this->fileXml .= $str;
        }
    }

    private function setPages($showLinks = 0)
    {
        // Страницы
        $f = [
            'visible' => 1,
            'limit' => self::MAX_URLS,
            'not_url' => '404'
        ];

        if ($showLinks) {
            $f['page'] = $this->page;
            $pages = $this->pages->get_pages($f);
        } else {
            $limit = $this->pages->get_pages($f, true);
            $f['limit'] = $limit;
            $pages = $this->pages->get_pages($f);
        }
        foreach ($pages as $p) {
            $lastModify = array();
            if ($p->url == 'blog') {
                $this->db->query("SELECT b.last_modify FROM __blog b");
                $lastModify = $this->db->results('last_modify');
                $lastModify[] = $this->settings->lastModifyPosts;
            }
            $lastModify[] = $p->last_modify;
            $lastModify = max($lastModify);
            $lastModify = substr($lastModify, 0, 10);

            if ($showLinks) {
                $params = [
                    'url' => $this->main_url . $p->url,
                    'changefreq' => 'daily',
                    'lastmod' => substr($p->last_modify, 0, 10),
                ];
                $this->writeItem($params);
            }
        }

        if (!$showLinks) {
            $this->setPagination('pages', $limit, $lastModify);
        }

    }

    private function setPagination($entity, $count, $lastModify)
    {
        $pagesCount = ceil($count / self::MAX_URLS);

        $page = 1;
        while ($page <= $pagesCount) {

            $url = $entity . (($page > 1 && $pagesCount > 1) ? '_page-' . $page : '') . '.xml';
            $param = [
                'url' => $this->main_url . 'sitemap/' . $url,
                'lastmod' => $lastModify,
            ];
            $page++;
            $this->writeMenuItem($param);
        }

    }

    private function setBlogs($showLinks = 0)
    {
        $f = [
            'visible' => 1,
            'limit' => self::MAX_URLS
        ];

        if ($showLinks) {
            $f['page'] = $this->page;
            $blogs = $this->blog->get_posts($f);
        } else {
            $blogsCount = $this->blog->count_posts($f);
            $f['limit'] = $blogsCount;
            $blogs = $this->blog->get_posts($f);
        }
        // Блог
        foreach ($blogs as $p) {
            $lastModify = substr($p->last_modify, 0, 10);
            if ($showLinks) {
                $params = [
                    'url' => $this->main_url . 'blog/' . $p->url,
                    'changefreq' => 'daily',
                    'lastmod' => $lastModify,
                ];
                $this->writeItem($params);
            }
        }
        if (!$showLinks) {
            $this->setPagination('blogs', $blogsCount, $lastModify);
        }
    }

    private function setCategories($showLinks = 0)
    {

        $limit = self::MAX_URLS;

        if ($showLinks) {
            $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($this->page - 1) * $limit, $limit);
            $query = $this->db->placehold("SELECT * FROM __categories c WHERE c.visible ORDER BY position $sql_limit");

        } else {
            $query = $this->db->placehold("SELECT COUNT(c.id) as count FROM __categories c WHERE c.visible  ORDER BY c.position");
            $this->db->query($query);
            $limit = intval($this->db->result('count'));

            $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($this->page - 1) * $limit, $limit);
            $query = $this->db->placehold("SELECT * FROM __categories c WHERE c.visible ORDER BY position $sql_limit");
        }
        $this->db->query($query);
        $categories = $this->db->results();

        // Категории
        $categoryIds = [];
        $lastModify = [];
        foreach ($categories as $c) {
            $lastModify[] = $c->last_modify;
            $categoryIds[] = intval($c->id);
            if ($showLinks) {
                $params = [
                    'url' => $this->main_url . 'catalog/' . $c->url,
                    'changefreq' => 'daily',
                    'priority' => '1.0',
                    'lastmod' => substr($c->last_modify, 0, 10),
                ];
                $this->writeItem($params);
            }

        }

        if (!$showLinks) {
            if (!empty($categoryIds) && is_array($categoryIds)) {
                $query = $this->db->placehold("SELECT p.last_modify FROM __products p
            INNER JOIN __products_categories pc ON pc.product_id = p.id AND pc.category_id in(?@)
            WHERE 1
            GROUP BY p.id ORDER BY p.last_modify DESC Limit 1", $categoryIds);


                $this->db->query($query);
                $res = $this->db->result('last_modify');
                if ($res) {
                    $lastModify[] = $res;
                }
            }

            $lastModify = substr(max($lastModify), 0, 10);
            $this->setPagination('categories', $limit, $lastModify);
        }

    }

    private function setBrands($showLinks = 0)
    {
        // Бренды

        $f = [
            'visible' => 1,
            'limit' => self::MAX_URLS
        ];

        if ($showLinks) {
            $f['page'] = $this->page;
            $brands = $this->brands->get_brands($f);
        } else {
            $brandsCount = $this->brands->count_brands($f);
            $f['limit'] = $brandsCount;
            $brands = $this->brands->get_brands($f);
        }
        $lastModify = [];
        $brandsIds = [];
        foreach ($brands as $b) {
            $lastModify[] = $b->last_modify;
            $brandsIds[] = intval($b->id);
            if ($showLinks) {
                $params = [
                    'url' => $this->main_url . 'brands/' . $b->url,
                    'changefreq' => 'daily',
                    'lastmod' => substr($b->last_modify, 0, 10),
                ];
                $this->writeItem($params);
            }
        }

        if (!$showLinks) {

            if (!empty($brandsIds) && is_array($brandsIds)) {
                $query = $this->db->placehold("SELECT p.last_modify FROM __products p  
                WHERE p.brand_id in(?@) ORDER BY p.last_modify DESC Limit 1",
                    $brandsIds);
                $this->db->query($query);
                $res = $this->db->result('last_modify');
                if ($res) {
                    $lastModify[] = $res;
                }
            }

            $lastModify = substr(max($lastModify), 0, 10);
            $this->setPagination('brands', $brandsCount, $lastModify);
        }
    }

    private function setProducts($showLinks = 0)
    {

        $f = [
            'visible' => 1,
            'limit' => self::MAX_URLS
        ];

        if ($showLinks) {
            $f['page'] = $this->page;
            $products = $this->products->get_products($f);
            foreach ($products as $p) {
                $params = [
                    'url' => $this->main_url . 'products/' . $p->url,
                    'changefreq' => 'daily',
                    'lastmod' => substr($p->last_modify, 0, 10),
                ];
                $this->writeItem($params);
            }
        } else {
            $productsCount = $this->products->count_products($f);

            $query = $this->db->placehold("SELECT url, last_modify FROM __products WHERE visible=1 ORDER BY last_modify DESC Limit 1");
            $this->db->query($query);

            $lastModify = $this->db->result('last_modify');
            $lastModify = substr($lastModify, 0, 10);
            $this->setPagination('products', $productsCount, $lastModify);
        }


    }

    private function setCategoriesFeatures($showLinks = 0)
    {

        $limit = self::MAX_URLS;

        $query = $this->db->placehold("SELECT * FROM __categories c WHERE c.visible ORDER BY position ");
        $this->db->query($query);
        $categories = $this->db->results();

        $count = 0;
        $values = [];
        foreach ($categories as $c) {
            $lastModify[] = $c->last_modify;
            foreach ($this->features_values->get_features_values(['category_id' => $c->id]) as $fv) {
                $count++;
                if ($showLinks) {
                    if ($count >= ($this->page - 1) * $limit && $count <= (($this->page - 1) * $limit) + $limit) {
                        $params = [
                            'url' => $this->main_url . 'catalog/' . $c->url . '/' . $fv->url . '-' . $fv->translit,
                            'changefreq' => 'daily',
                            'lastmod' => substr($c->last_modify, 0, 10),
                        ];
                        $this->writeItem($params);

                    }
                }
                $values[] = $fv;
            }
        }
        if (!$showLinks) {
            $lastModify = substr(max($lastModify), 0, 10);
            $this->setPagination('categories-features', $count, $lastModify);
        }


    }

    private function setPage($params)
    {
        list($key, $value) = explode('-', $params);

        switch ($key) {
            case 'page':
                if (intval($value)) {
                    $this->page = intval($value);
                    return;
                }
                break;
        }

        $this->page = 1;

    }

    private function setCategoriesBrands($showLinks = 0)
    {
        if ($showLinks) {
            $limit = self::MAX_URLS;
            $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($this->page - 1) * $limit, $limit);
            $fields = 'c.url as category_url, b.url as brand_url';
        } else {
            $fields = 'b.last_modify';
            $sql_limit = '';
        }

        $query = $this->db->placehold("
            SELECT $fields FROM `__brands` b
                INNER JOIN __products p ON p.brand_id = b.id and p.visible
                INNER JOIN __products_categories pc ON pc.product_id = p.id
                INNER JOIN __categories c ON c.id = pc.category_id AND c.visible
            WHERE b.visible GROUP BY b.id  $sql_limit");
        $this->db->query($query);

        $items = $this->db->results();

        $lastModify = [];
        $count = 0;
        foreach ($items as $item) {
            $lastModify[] = $item->last_modify;
            $count++;
            if ($showLinks) {
                $params = [
                    'url' => $this->main_url . 'catalog/' . $item->category_url . '/brand-' . $item->brand_url,
                    'changefreq' => 'daily',
                    'lastmod' => substr($item->category_last_modify, 0, 10),
                ];
                $this->writeItem($params);
            }
        }

        if (!$showLinks) {
            $lastModify = substr(max($lastModify), 0, 10);
            $this->setPagination('categories-brands', $count, $lastModify);
        }

    }

}