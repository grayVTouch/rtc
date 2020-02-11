<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/31
 * Time: 15:28
 */

namespace App\WebSocket\V1\Controller;


use App\WebSocket\V1\Action\ArticleAction;

class Article extends Auth
{
    // 帮助中心
    protected $articleTypeIdForHelpCenter = 1;

    // 关于我们
    protected $articleTypeIdForAboutUs = 2;

    // 使用协议
    protected $articleTypeIdForProtocol = 3;

    // 隐私条款
    protected $articleTypeIdForPrivacyPolicy = 4;

    // 加密技术
    protected $articleTypeIdForEncryption = 5;

    // 用户协议
    protected $articleTypeIdForUserProtocol = 6;

    // 关于我们
    public function aboutUs(array $param)
    {
        $res = ArticleAction::firstByArticleTypeId($this , $this->articleTypeIdForAboutUs , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 使用协议
    public function protocol(array $param)
    {
        $res = ArticleAction::firstByArticleTypeId($this , $this->articleTypeIdForProtocol , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 隐私条款
    public function privacyPolicy(array $param)
    {
        $res = ArticleAction::firstByArticleTypeId($this , $this->articleTypeIdForPrivacyPolicy , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 用户协议
    public function userProtocol(array $param)
    {
        $res = ArticleAction::firstByArticleTypeId($this , $this->articleTypeIdForUserProtocol , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 帮助中心-列表
    public function listForHelpCenter(array $param)
    {
        $param['page'] = $param['page'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = ArticleAction::listForArticle($this , $this->articleTypeIdForHelpCenter , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 帮助中心-文章详情
    public function detailForHelpCenter(array $param)
    {
        $param['id'] = $param['id'] ?? '';
        $res = ArticleAction::detailForArticle($this , $this->articleTypeIdForHelpCenter , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 帮助中心-文章详情
    public function encrypt(array $param)
    {
        $res = ArticleAction::firstByArticleTypeId($this , $this->articleTypeIdForEncryption , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}