<?php

/*
 * This file is part of the CCDNForum ForumBundle
 *
 * (c) CCDN (c) CodeConsortium <http://www.codeconsortium.com/>
 *
 * Available on github <http://www.github.com/codeconsortium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CCDNForum\ForumBundle\Form\Handler\User\Post;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpKernel\Debug\ContainerAwareTraceableEventDispatcher;

use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
//use CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminBoardEvent;

//use CCDNForum\ForumBundle\Manager\BaseManagerInterface;

use CCDNForum\ForumBundle\Entity\Topic;
use CCDNForum\ForumBundle\Entity\Post;

/**
 *
 * @category CCDNForum
 * @package  ForumBundle
 *
 * @author   Reece Fowell <reece@codeconsortium.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @version  Release: 2.0
 * @link     https://github.com/codeconsortium/CCDNForumForumBundle
 *
 */
class PostCreateFormHandler
{
    /**
     *
     * @access protected
     * @var \Symfony\Component\Form\FormFactory $factory
     */
    protected $factory;

    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Form\Type\PostType $formPostType
     */
    protected $formPostType;

    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Model\BaseModelInterface $postModel
     */
    protected $postModel;

    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Form\Type\PostType $form
     */
    protected $form;

    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Entity\Topic $topic
     */
    protected $topic;

    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Entity\Post $postToQuote
     */
    protected $postToQuote;

	/**
	 * 
	 * @access protected
	 * @var \Symfony\Component\HttpKernel\Debug\ContainerAwareTraceableEventDispatcher $dispatcher
	 */
	protected $dispatcher;

	/**
	 * 
	 * @access protected
	 * @var \Symfony\Component\HttpFoundation\Request $request
	 */
	protected $request;

	/**
	 * 
	 * @access protected
	 * @var \Symfony\Component\Security\Core\User\UserInterface
	 */
	protected $user;

    /**
     *
     * @access public
     * @param \Symfony\Component\HttpKernel\Debug\ContainerAwareTraceableEventDispatcher $dispatcher
     * @param \Symfony\Component\Form\FormFactory                 $factory
     * @param \CCDNForum\ForumBundle\Form\Type\PostType           $formPostType
     * @param \CCDNForum\ForumBundle\Model\BaseModelInterface     $postModel
     */
    public function __construct(ContainerAwareTraceableEventDispatcher $dispatcher, FormFactory $factory, $formPostType, $postModel)
    {
		$this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->formPostType = $formPostType;
        $this->postModel = $postModel;
    }

    /**
     *
     * @access public
     * @param  \Symfony\Component\Security\Core\User\UserInterface       $user
     * @return \CCDNForum\ForumBundle\Form\Handler\PostUpdateFormHandler
     */
	public function setUser(UserInterface $user)
	{
		$this->user = $user;
		
		return $this;
	}

    /**
     *
     * @access public
     * @param  \CCDNForum\ForumBundle\Entity\Topic                       $topic
     * @return \CCDNForum\ForumBundle\Form\Handler\PostCreateFormHandler
     */
    public function setTopic(Topic $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     *
     * @access public
     * @param  \CCDNForum\ForumBundle\Entity\Post                        $post
     * @return \CCDNForum\ForumBundle\Form\Handler\PostCreateFormHandler
     */
    public function setPostToQuote(Post $post)
    {
        $this->postToQuote = $post;

        return $this;
    }

    /**
     *
     * @access public
     * @param  \Symfony\Component\HttpFoundation\Request $request
     */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

    /**
     *
     * @access public
     * @return bool
     */
    public function process()
    {
        $this->getForm();

        if ($this->request->getMethod() == 'POST') {
            $this->form->bind($this->request);

            // Validate
            if ($this->form->isValid()) {
                $formData = $this->form->getData();

                if ($this->getSubmitAction() == 'post') {
                    $this->onSuccess($formData);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * @access public
     * @return string
     */
    public function getSubmitAction()
    {
        if ($this->request->request->has('submit')) {
            $action = key($this->request->request->get('submit'));
        } else {
            $action = 'post';
        }

        return $action;
    }

    /**
     *
     * @access public
     * @return string
     */
    protected function getQuote()
    {
        $quote = "";

        if (is_object($this->postToQuote) && $this->postToQuote instanceof Post) {
            $author = $this->postToQuote->getCreatedBy();
            $body = $this->postToQuote->getBody();

            $quote = '[QUOTE="' . $author . '"]' . $body . '[/QUOTE]';
        }

        return $quote;
    }

    /**
     *
     * @access public
     * @return Form
     */
    public function getForm()
    {
        if (null == $this->form) {
            if (! is_object($this->topic) || ! ($this->topic instanceof Topic)) {
                throw new \Exception('Topic must be specified to create a Reply in PostCreateFormHandler');
            }

            $post = new Post();
            $post->setTopic($this->topic);
            $post->setBody($this->getQuote());

            $this->form = $this->factory->create($this->formPostType, $post);
        }

        return $this->form;
    }

    /**
     *
     * @access protected
     * @param  \CCDNForum\ForumBundle\Entity\Post         $post
     * @return \CCDNForum\ForumBundle\Manager\PostManager
     */
    protected function onSuccess(Post $post)
    {
        $post->setCreatedDate(new \DateTime());
        $post->setCreatedBy($this->user);
        $post->setTopic($this->topic);
        $post->setIsLocked(false);
        $post->setIsDeleted(false);

        return $this->postModel->postTopicReply($post)->flush();
    }
}
