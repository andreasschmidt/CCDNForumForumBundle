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

namespace CCDNForum\ForumBundle\Model\Model;

use CCDNForum\ForumBundle\Model\Model\ModelInterface;
use CCDNForum\ForumBundle\Model\Manager\ManagerInterface;
use CCDNForum\ForumBundle\Model\Repository\RepositoryInterface;

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
 * @abstract
 */
abstract class BaseModel implements ModelInterface
{
    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Model\Repository\RepositoryInterface
     */
    protected $repository;

    /**
     *
     * @access protected
     * @var \CCDNForum\ForumBundle\Model\Manager\ManagerInterface
     */
    protected $manager;

    /**
     *
     * @access public
     * @param \CCDNForum\ForumBundle\Model\Repository\RepositoryInterface $repository
     * @param \CCDNForum\ForumBundle\Model\Manager\ManagerInterface       $manager
     */
    public function __construct(RepositoryInterface $repository, ManagerInterface $manager)
    {
        $repository->setModel($this);
        $this->repository = $repository;

        $manager->setModel($this);
        $this->manager = $manager;
    }

    /**
     *
     * @access public
     * @return \CCDNForum\ForumBundle\Model\Repository\RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     *
     * @access public
     * @return \CCDNForum\ForumBundle\Model\Manager\ManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }
}
