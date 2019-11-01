<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/31
 * Time: 15:28
 */

namespace App\WebSocket;


use App\WebSocket\Action\ArticleAction;

class Article extends Auth
{
    // 关于我们
    protected $articleTypeIdForAboutUs = 2;

    // 帮助中心
    protected $articleTypeIdForHelpCenter = 1;

    // 使用协议
    protected $articleTypeIdForProtocol = 3;

    // 隐私条款
    protected $articleTypeIdForPrivacyPolicy = 4;

    // 关于我们
    public function aboutUs(array $param)
    {
        $res = ArticleAction::firstByArticleTypeId($this , $this->articleTypeIdForAboutUs , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 使用协议
    public function protocol(array $param)
    {
        $res = ArticleAction::firstByArticleTypeId($this , $this->articleTypeIdForProtocol , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 隐私条款
    public function privacyPolicy(array $param)
    {
        $res = ArticleAction::firstByArticleTypeId($this , $this->articleTypeIdForPrivacyPolicy , $param);
        if ($res['code'] != 200) {
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
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 帮助中心-文章详情
    public function detailForHelpCenter(array $param)
    {
        $param['id'] = $param['id'] ?? '';
        $res = ArticleAction::detailForArticle($this , $this->articleTypeIdForHelpCenter , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}