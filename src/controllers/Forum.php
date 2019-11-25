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

    $categories = Forum::getInstance()->getCategories();
    return new Response(
        json_encode(array('categories' => $categories))
    );
});

$router->post('/api/forum/categories', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getBody();

    if(!isset($data['name']) || empty($data['name']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NO_NAME_GIVEN');
    if(!isset($data['description']) || empty($data['description']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NO_DESCRIPTION_GIVEN');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $name = $data['name'];
    $description = $data['description'];

    $display_order = Forum::getInstance()->getNewCategoryDisplayOrder();

    $category = Forum::getInstance()->createCategory($name, $description, $display_order);
    if($category) {
        return new Response(
            json_encode(array('category' => $category)),
            201
        );
    } else {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CREATE_CATEGORY_ERROR'), 500);
    }
});

$router->post('/api/forum/categories/<category_id:int>/reorder', function($request, $category_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getBody();

    if(!isset($data['direction']) || empty($data['direction']))
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_REORDER_CATEGORY_NO_DIRECTION'), 400);

    $direction = $data['direction'];

    $category = Forum::getInstance()->getCategory($category_id);
    if (!$category) {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND'), 404);
    }

    $order_sibling = Forum::getInstance()->getSiblingCategoryOrder($category['display_order'], $direction);

    $category_sibling = Forum::getInstance()->getCategoryFromDisplayOrder($order_sibling);

    if ($category_sibling) {
        if(!Forum::getInstance()->updateCategoryDisplayOrder($category_sibling['id'], $category['display_order']))
            return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_REORDER_CATEGORY_ERROR'), 500);
    } else
        $order_sibling = $category['display_order'];

    if(!Forum::getInstance()->updateCategoryDisplayOrder($category['id'], $order_sibling))
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_REORDER_CATEGORY_ERROR'), 500);

    return new Response('', 204);
});

$router->delete('/api/forum/categories/<category_id:int>', function($request, $category_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $category = Forum::getInstance()->getCategory($category_id);
    if (!$category) {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND'), 404);
    }

    if (Forum::getInstance()->deleteCategory($category_id)) {
        return new Response(
            '',
            204
        );
    } else {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_DELETE_CATEGORY_ERROR'), 500);
    }
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

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $category = Forum::getInstance()->getCategory($category_id);
    if (!$category) {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND'), 404);
    }

    $page = $data['page'];
    $pageSize = $data['pageSize'];

    $paginator = new Paginator($page, $pageSize);
    $posts = $paginator->paginate(Forum::getInstance()->getPosts($category_id));

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
    $data = $request->getBody();

    if(!isset($data['title']) || empty($data['title']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_POST_NO_TITLE');
    if(!isset($data['content']) || empty($data['content']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_POST_NO_CONTENT');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $title = $data['title'];
    $content = $data['content'];

    $post = Forum::getInstance()->createPost($_SESSION['id'], $category_id, $title, $content);
    if($post) {
        return new Response(
            json_encode(array('post' => $post)),
            201
        );
    } else {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_POST_CREATE_ERROR'), 500);
    }
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

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $post = Forum::getInstance()->getPost($post_id);
    if (!$post) {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND'), 404);
    }

    $page = $data['page'];
    $pageSize = $data['pageSize'];

    $paginator = new Paginator($page, $pageSize);
    $responses = $paginator->paginate(Forum::getInstance()->getPostResponses($post_id));

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
    $data = $request->getBody();

    if(!isset($data['content']) || empty($data['content']))
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_RESPONSE_NO_CONTENT');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $content = $data['content'];

    $post = Forum::getInstance()->getPost($post_id);
    if (!$post) {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_POST_NOT_FOUND'), 404);
    }

    $response = Forum::getInstance()->createPost($_SESSION['id'], $post['category'], $post['title'], $content, $post['id']);
    if($response) {
        return new Response(
            json_encode(array('response' => $response)),
            201
        );
    } else {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_RESPONSE_CREATE_ERROR'), 500);
    }
});

$router->delete('/api/forum/posts/<post_id:int>/responses/<response_id:int>', function($request, $post_id, $response_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $response = Forum::getInstance()->getPostResponse($post_id, $response_id);

    if (!$response) {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_RESPONSE_NOT_FOUND'), 404);
    }

    if (intval($response['author']['id']) !== $_SESSION['id'] && intval($_SESSION['role']) < Role::$ROLES['ADMINISTRATOR']) {
        return API::makeResponseError(I18n::getInstance('API_FORUM_RESPONSE_NOACCESS'), 403);
    }

    if (Forum::getInstance()->deletePostResponse($post_id, $response_id)) {
        return new Response(
            '',
            204
        );
    } else {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_DELETE_RESPONSE_ERROR'), 500);
    }
});

$router->delete('/api/forum/posts/<post_id:int>', function($request, $post_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $post = Forum::getInstance()->getPost($post_id);

    if (!$post)
        return API::makeResponseError("Post not found!", 404);

    if (intval($post['author']['id']) !== $_SESSION['id'] && intval($_SESSION['role']) < Role::$ROLES['ADMINISTRATOR'])
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_POST_NOACCESS'), 403);

    if (Forum::getInstance()->deletePost($post_id)) {
        return new Response(
            '',
            204
        );
    } else {
        return API::makeResponseError(I18n::getInstance()->translate('API_FORUM_DELETE_POST_ERROR'), 500);
    }
});
