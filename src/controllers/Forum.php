<?php

use Testify\Router\Router;
use Testify\Router\Response;
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

    $categories = Forum::getCategories();
    if ($categories === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_GET_CATEGORIES_ERROR'), 500);

    return new Response(
        json_encode(array('categories' => $categories))
    );
});

$router->post('/api/forum/categories', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('name'))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NO_NAME_GIVEN');
    if(!$data->existAndNotEmpty('description'))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NO_DESCRIPTION_GIVEN');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $name = $data->get('name');
    $description = $data->get('description');

    $display_order = Forum::getNewCategoryDisplayOrder();
    if ($display_order === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CREATE_CATEGORY_ERROR'), 500);

    $category = Forum::createCategory($name, $description, $display_order);
    if($category === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CREATE_CATEGORY_ERROR'), 500);

    return new Response(
        json_encode(array('category' => $category)),
        201
    );
});

$router->post('/api/forum/categories/<category_id:int>/reorder', function($request, $category_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('direction'))
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_REORDER_CATEGORY_NO_DIRECTION'), 400);

    $direction = $data->get('direction');

    $category = Forum::getCategory($category_id);
    if ($category === FALSE) {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND'), 404);
    }

    $order_sibling = Forum::getSiblingCategoryOrder($category['display_order'], $direction);
    if ($order_sibling === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_REORDER_CATEGORY_ERROR'), 500);

    $category_sibling = Forum::getCategoryFromDisplayOrder($order_sibling);

    if ($category_sibling !== FALSE) {
        if(!Forum::updateCategoryDisplayOrder($category_sibling['id'], $category['display_order']))
            return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_REORDER_CATEGORY_ERROR'), 500);
    } else
        $order_sibling = $category['display_order'];

    if(!Forum::updateCategoryDisplayOrder($category['id'], $order_sibling))
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_REORDER_CATEGORY_ERROR'), 500);

    return new Response('', 204);
});

$router->delete('/api/forum/categories/<category_id:int>', function($request, $category_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $category = Forum::getCategory($category_id);
    if ($category === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND'), 404);

    if (Forum::deleteCategory($category_id) === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_DELETE_CATEGORY_ERROR'), 500);

    return new Response(
        '',
        204
    );
});

$router->get('/api/forum/categories/<category_id:int>/posts', function($request, $category_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('page'))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NOPAGE');
    if(!$data->existAndNotEmpty('pageSize'))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $category = Forum::getCategory($category_id);
    if ($category === FALSE) {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND'), 404);
    }

    $page = $data->get('page');
    $pageSize = $data->get('pageSize');

    $paginator = new Paginator($page, $pageSize);
    $posts = Forum::getPosts($category_id);
    if ($posts === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_GET_POSTS_ERROR'), 500);

    $posts = $paginator->paginate($posts);

    return new Response(
        json_encode(array(
            'category' => $category,
            'posts' => $posts['data'],
            'paginator' => $posts['paginator']
        ))
    );
});

$router->post('/api/forum/categories/<category_id:int>/posts', function($request, $category_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('title'))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_POST_NO_TITLE');
    if(!$data->existAndNotEmpty('content'))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_POST_NO_CONTENT');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $title = $data->get('title');
    $content = $data->get('content');

    $post = Forum::createPost($_SESSION['id'], $category_id, $title, $content);
    if($post === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_POST_CREATE_ERROR'), 500);

    return new Response(
        json_encode(array('post' => $post)),
        201
    );
});

$router->get('/api/forum/posts/<post_id:int>/responses', function($request, $post_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('page'))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NOPAGE');
    if(!$data->existAndNotEmpty('pageSize'))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $post = Forum::getPost($post_id);
    if ($post === FALSE) {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND'), 404);
    }

    $page = $data->get('page');
    $pageSize = $data->get('pageSize');

    $paginator = new Paginator($page, $pageSize);
    $responses = Forum::getPostResponses($post_id);

    if ($responses === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_GET_RESPONSES_ERROR'), 500);

    $responses = $paginator->paginate($responses);

    return new Response(
        json_encode(array(
            'post' => $post,
            'responses' => $responses['data'],
            'paginator' => $responses['paginator']
        ))
    );
});

$router->post('/api/forum/posts/<post_id:int>/responses', function($request, $post_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('content'))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_RESPONSE_NO_CONTENT');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $content = $data->get('content');

    $post = Forum::getPost($post_id);
    if ($post === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_POST_NOT_FOUND'), 404);

    $response = Forum::createPost($_SESSION['id'], $post['category'], $post['title'], $content, $post['id']);
    if($response === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_RESPONSE_CREATE_ERROR'), 500);

    return new Response(
        json_encode(array('response' => $response)),
        201
    );
});

$router->delete('/api/forum/posts/<post_id:int>/responses/<response_id:int>', function($request, $post_id, $response_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $response = Forum::getPostResponse($post_id, $response_id);

    if ($response === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_RESPONSE_NOT_FOUND'), 404);

    if (intval($response['author']['id']) !== $_SESSION['id'] && intval($_SESSION['role']) < Role::$ROLES['ADMINISTRATOR']) {
        return API::makeResponseError(I18n::getInstance('API_FORUM_RESPONSE_NOACCESS'), 403);
    }

    if (Forum::deletePostResponse($post_id, $response_id) === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_DELETE_RESPONSE_ERROR'), 500);

    return new Response(
        '',
        204
    );
});

$router->delete('/api/forum/posts/<post_id:int>', function($request, $post_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $post = Forum::getPost($post_id);

    if ($post === FALSE)
        return API::makeResponseError("Post not found!", 404);

    if (intval($post['author']['id']) !== $_SESSION['id'] && intval($_SESSION['role']) < Role::$ROLES['ADMINISTRATOR'])
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_POST_NOACCESS'), 403);

    if (Forum::deletePost($post_id) === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_DELETE_POST_ERROR'), 500);

    return new Response(
        '',
        204
    );
});
