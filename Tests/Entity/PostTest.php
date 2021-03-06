<?php

namespace Hypebeast\WordpressBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Hypebeast\WordpressBundle\Entity\Comment;
use Hypebeast\WordpressBundle\Entity\Post;
use Hypebeast\WordpressBundle\Entity\User;

class PostTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    protected function setUp()
    {
        parent::setUp();

        $kernel = static::createKernel();
        $kernel->boot();

        $this->em = $kernel->getContainer()->get('doctrine')->getEntityManager();

        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown()
    {
        $this->em->getConnection()->rollback();

        parent::tearDown();
    }

    /**
     * Create post test
     *
     * @dataProvider postProvider
     */
    public function testNewPost($title, $content, $userId)
    {
        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setUser($this->getUserRepository()->find($userId));

        $this->em->persist($post);
        $this->em->flush();

        $result = $this->getPostRepository()
            ->createQueryBuilder('post')
            ->orderBy('post.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($title, $result->getTitle());
        $this->assertEquals($content, $result->getContent());
        $this->assertEquals($userId, $result->getUser()->getId());
        $this->assertInternalType('string', $result->getUser()->getMetas()->get(1)->getKey());

        return $post;
    }

    /**
     * Create post test
     *
     * @dataProvider postProvider
     */
    public function testPostExcerpt($title, $content, $userId)
    {
        $post = new Post();
        $post->setTitle($title);
        $post->setExcerptLength(30);
        $post->setContent($content);

        $this->assertLessThanOrEqual(30, strlen($post->getExcerpt()));
    }

    /**
     * Test post slug
     *
     * @dataProvider postProvider
     */
    public function testPostSlug($title, $content, $userId)
    {
        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);

        $this->em->persist($post);
        $this->em->flush();
        $this->em->clear();

        $this->assertEquals(
            1,
            preg_match('/^[0-9a-z_-]+$/', $post->getSlug()),
            'Post slug "'.$post->getSlug().'" should only contain numbers, lowercase characters dash, and underscore.'
        );
    }

    /**
     * Create comment test
     *
     * @dataProvider commentProvider
     */
    public function testNewComment($author, $authorEmail, $content, $userId, $parentId)
    {
        $post   = $this->getPostRepository()->findOneById(1);
        $user   = null;
        $parent = null;

        if($userId !== null) {
            $user = $this->getUserRepository()->find($userId);
        }

        if($parentId !== null) {
            $parent = $this->getCommentRepository()->find($parentId);
        }

        $comment = new Comment();
        $comment->setContent($content);

        if($parent) {
            $comment->setParent($parent);
        }

        if($user) {
            $comment->setUser($user);
        } else {
            $comment->setAuthor($author);
            $comment->setAuthorEmail($authorEmail);
        }

        $post->addComment($comment);

        $this->em->persist($post);
        $this->em->flush();

        // start assert
        $this->assertCount($post->getCommentCount(), $post->getComments());
        $this->assertEquals($content, $post->getComments()->last()->getContent());
        $this->assertEquals($user, $post->getComments()->last()->getUser());

        if($parent) {
            $this->assertEquals($parent, $post->getComments()->last()->getParent());
        }

        if($user) {
            $this->assertEquals($user->getDisplayName(), $post->getComments()->last()->getAuthor());
            $this->assertEquals($user->getEmail(), $post->getComments()->last()->getAuthorEmail());
        } else {
            $this->assertEquals($author, $post->getComments()->last()->getAuthor());
            $this->assertEquals($authorEmail, $post->getComments()->last()->getAuthorEmail());
        }
    }

    /**
     * Get page meta
     */
    public function testGetPostMetasByKey()
    {
        $page = $this->getPostRepository()->findOneByType('page');

        $this->assertEquals(
            $page->getMetasByKey($page->getMetas()->get(0)->getKey())->first()->getValue(),
            $page->getMetas()->get(0)->getValue()
        );
    }

    /**
     * Create new post with a non-exist user id
     *
     * @expectedException ErrorException
     */
    public function testNewPostWithNonExistUser()
    {
        $this->testNewPost('Lorem ipsum dolor sit amet', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.', 999);
    }

    public function postProvider()
    {
        return array(
            array('Lorem ipsum dolor sit amet', 'Lorem ipsum <strong>dolor sit amet</strong>, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.', 1),
            array('Sed ut perspiciatis unde', 'Sed ut perspiciatis unde <em>omnis iste natus</em> error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.', 1),
            array('Sed \ [ ] % ^!@#$%^&* fsad', 'Aenean commodo ligula eget dolor. Aenean massa. ', 1)
        );
    }

    public function commentProvider()
    {
        return array(
            array('Peter', 'peter@example.com', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.', null, null),
            array('Mary', 'peter@example.com', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.', null, 1),
            array('Tom', 'tom@example.com', 'Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.', 1, null),
        );
    }

    protected function getPostRepository()
    {
        return $this->em->getRepository('HypebeastWordpressBundle:Post');
    }

    protected function getCommentRepository()
    {
        return $this->em->getRepository('HypebeastWordpressBundle:Comment');
    }

    protected function getUserRepository()
    {
        return $this->em->getRepository('HypebeastWordpressBundle:User');
    }
}