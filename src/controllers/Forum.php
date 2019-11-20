<?php

use Testify\Router\Router;
use Testify\Model\Forum;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\Paginator;
use Testify\Component\I18n;

$router = Router::getInstance();


$router->get('/api/forum/categories', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $categories = Forum::getInstance()->getCategories();
    return json_encode(array("r" => True, "categories" => $categories));
});

$router->post('/api/forum/categories', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getBody();

    if(!isset($data['name']) || empty($data['name']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NO_NAME_GIVEN');

    if(count($errors_arr) === 0) {
        $name = $data['name'];

        $category = Forum::getInstance()->createCategory($name);
        return json_encode(array("r" => True, "category" => $category));
    } else
        return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/forum/categories/<category_id:int>/posts', function($request, $category_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getBody();

    if(!isset($data['page']) || empty($data['page']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NOPAGE');
    if(!isset($data['pageSize']) || empty($data['pageSize']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NOSIZEPAGE');

    if (count($errors_arr) === 0) {
        $category = Forum::getInstance()->getCategory($category_id);
        if ($category === null)
            $errors_arr[] = I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND');
    }

    if (count($errors_arr) === 0) {
        $page = $data['page'];
        $pageSize = $data['pageSize'];

        $paginator = new Paginator($page, $pageSize);
        $posts = $paginator->paginate(Forum::getInstance()->getPosts($category_id));

        return json_encode(array(
            "r" => True,
            "category" => $category,
            "posts" => $posts['data'],
            "paginator" => $posts['paginator']
        ));
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->post('/api/forum/categories/<category_id:int>/posts', function($request, $category_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getBody();

    if(!isset($data['title']) || empty($data['title']))
        $errors_arr[] = "";
    if(!isset($data['content']) || empty($data['content']))
        $errors_arr[] = "";

    if(count($errors_arr) === 0) {
        $title = $data['title'];
        $content = $data['content'];

        $post = Forum::getInstance()->createPost($_SESSION['id'], $category_id, $title, $content);
        return json_encode(array("r" => True, "post" => $post));
    } else
        return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/forum/posts/<post_id:int>/responses', function($request, $post_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getBody();

    if(!isset($data['page']) || empty($data['page']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NOPAGE');
    if(!isset($data['pageSize']) || empty($data['pageSize']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NOSIZEPAGE');

    if (count($errors_arr) === 0) {
        $post = Forum::getInstance()->getPost($post_id);
        if ($post === null)
            $errors_arr[] = I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND');
    }

    if (count($errors_arr) === 0) {
        $page = $data['page'];
        $pageSize = $data['pageSize'];

        $paginator = new Paginator($page, $pageSize);
        $responses = $paginator->paginate(Forum::getInstance()->getPostResponses($post_id));

        return json_encode(array(
            "r" => True,
            "post" => $post,
            "responses" => $responses['data'],
            "paginator" => $responses['paginator']
        ));
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});
