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

namespace CCDNForum\ForumBundle\Form\Handler\Admin\Category;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher ;

use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminCategoryEvent;
use CCDNForum\ForumBundle\Form\Handler\BaseFormHandler;
use CCDNForum\ForumBundle\Model\Model\ModelInterface;
use CCDNForum\ForumBundle\Entity\Forum;
use CCDNForum\ForumBundle\Entity\Category;

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
class CategoryCreateFormHandler extends BaseFormHandler
{
    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Form\Type\Admin\Category\CategoryCreateFormType $categoryCreateFormType
     */
    protected $categoryCreateFormType;

    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Model\Model\CategoryModel $categoryModel
     */
    protected $categoryModel;

    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Entity\Forum $defaultForum
     */
    protected $defaultForum;

    /**
     *
     * @access public
     * @param \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher  $dispatcher
     * @param \Symfony\Component\Form\FormFactory                                        $factory
     * @param \CCDNForum\ForumBundle\Form\Type\Admin\Category\CategoryCreateFormType     $categoryCreateFormType
     * @param \CCDNForum\ForumBundle\Model\Model\CategoryModel                           $categoryModel
     */
    public function __construct(ContainerAwareEventDispatcher  $dispatcher, FormFactory $factory, $categoryCreateFormType, ModelInterface $categoryModel)
    {
        $this->factory = $factory;
        $this->categoryCreateFormType = $categoryCreateFormType;
        $this->categoryModel = $categoryModel;
        $this->dispatcher = $dispatcher;
    }

    /**
     *
     * @access public
     * @param  \CCDNForum\ForumBundle\Entity\Forum                                          $forum
     * @return \CCDNForum\ForumBundle\Form\Handler\Admin\Category\CategoryCreateFormHandler
     */
    public function setDefaultForum(Forum $forum)
    {
        $this->defaultForum = $forum;

        return $this;
    }

    /**
     *
     * @access public
     * @return \Symfony\Component\Form\Form
     */
    public function getForm()
    {
        if (null == $this->form) {
            $category = new Category();

            $options = array(
                'default_forum' => $this->defaultForum
            );

            $this->dispatcher->dispatch(ForumEvents::ADMIN_CATEGORY_CREATE_INITIALISE, new AdminCategoryEvent($this->request, $category));

            $this->form = $this->factory->create($this->categoryCreateFormType, $category, $options);
        }

        return $this->form;
    }

    /**
     *
     * @access protected
     * @param  \CCDNForum\ForumBundle\Entity\Category           $category
     * @return \CCDNForum\ForumBundle\Model\Model\CategoryModel
     */
    protected function onSuccess(Category $category)
    {
        $this->dispatcher->dispatch(ForumEvents::ADMIN_CATEGORY_CREATE_SUCCESS, new AdminCategoryEvent($this->request, $category));

        $this->categoryModel->saveNewCategory($category);

        $this->dispatcher->dispatch(ForumEvents::ADMIN_CATEGORY_CREATE_COMPLETE, new AdminCategoryEvent($this->request, $category));

        return $this->categoryModel;
    }
}
