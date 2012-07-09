<?php

namespace Hypebeast\WordpressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Hypebeast\WordpressBundle\Entity\Post
 *
 * @ORM\Table(name="posts")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Post
{
    /**
     * @var bigint $id
     *
     * @ORM\Column(name="ID", type="wordpressid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="post_date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var datetime $dateGmt
     *
     * @ORM\Column(name="post_date_gmt", type="datetime", nullable=false)
     */
    private $dateGmt;

    /**
     * @var text $content
     *
     * @ORM\Column(name="post_content", type="text", nullable=false)
     */
    private $content;

    /**
     * @var text $title
     *
     * @ORM\Column(name="post_title", type="text", nullable=false)
     */
    private $title;

    /**
     * @var text $excerpt
     *
     * @ORM\Column(name="post_excerpt", type="text", nullable=false)
     */
    private $excerpt;

    /**
     * @var int excerpt length
     */
    private $excerptLength = 100;

    /**
     * @var string $status
     *
     * @ORM\Column(name="post_status", type="string", length=20, nullable=false)
     */
    private $status = "publish";

    /**
     * @var string $commentStatus
     *
     * @ORM\Column(name="comment_status", type="string", length=20, nullable=false)
     */
    private $commentStatus = "open";

    /**
     * @var string $pingStatus
     *
     * @ORM\Column(name="ping_status", type="string", length=20, nullable=false)
     */
    private $pingStatus = "open";

    /**
     * @var string $password
     *
     * @ORM\Column(name="post_password", type="string", length=20, nullable=false)
     */
    private $password = "";

    /**
     * @var string $slug
     *
     * @ORM\Column(name="post_name", type="string", length=200, nullable=false)
     * @Gedmo\Slug(fields={"title"})
     */
    private $slug;

    /**
     * @var text $toPing
     *
     * @ORM\Column(name="to_ping", type="text", nullable=false)
     */
    private $toPing = "";

    /**
     * @var text $pinged
     *
     * @ORM\Column(name="pinged", type="text", nullable=false)
     */
    private $pinged = "";

    /**
     * @var datetime $modifiedDate
     *
     * @ORM\Column(name="post_modified", type="datetime", nullable=false)
     */
    private $modifiedDate;

    /**
     * @var datetime $modifiedDateGmt
     *
     * @ORM\Column(name="post_modified_gmt", type="datetime", nullable=false)
     */
    private $modifiedDateGmt;

    /**
     * @var text $contentFiltered
     *
     * @ORM\Column(name="post_content_filtered", type="text", nullable=false)
     */
    private $contentFiltered = "";

    /**
     * @var bigint $parent
     *
     * @ORM\OneToOne(targetEntity="Post")
     * @ORM\JoinColumn(name="post_parent", referencedColumnName="ID")
     */
    private $parent = 0;

    /**
     * @var string $guid
     *
     * @ORM\Column(name="guid", type="string", length=255, nullable=false)
     */
    private $guid = "";

    /**
     * @var integer $menuOrder
     *
     * @ORM\Column(name="menu_order", type="integer", length=11, nullable=false)
     */
    private $menuOrder = 0;

    /**
     * @var string $type
     *
     * @ORM\Column(name="post_type", type="string", nullable=false)
     */
    private $type = "post";

    /**
     * @var string $mimeType
     *
     * @ORM\Column(name="post_mime_type", type="string", length=100, nullable=false)
     */
    private $mimeType = "";

    /**
     * @var bigint $commentCount
     *
     * @ORM\Column(name="comment_count", type="bigint", length=20, nullable=false)
     */
    private $commentCount = 0;

    /**
     * @var Hypebeast\WordpressBundle\Entity\PostMeta
     *
     * @ORM\OneToMany(targetEntity="Hypebeast\WordpressBundle\Entity\PostMeta", mappedBy="post")
     */
    private $metas;

    /**
     * @var Hypebeast\WordpressBundle\Entity\Comment
     *
     * @ORM\OneToMany(targetEntity="Hypebeast\WordpressBundle\Entity\Comment", mappedBy="post", cascade={"persist"})
     */
    private $comments;

    /**
     * @var Hypebeast\WordpressBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Hypebeast\WordpressBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="post_author", referencedColumnName="ID")
     * })
     */
    private $user;

    /**
     * @var Hypebeast\WordpressBundle\Entity\Taxonomy
     *
     * @ORM\ManyToMany(targetEntity="Hypebeast\WordpressBundle\Entity\Taxonomy", inversedBy="posts")
     * @ORM\JoinTable(name="wp_term_relationships",
     *   joinColumns={
     *     @ORM\JoinColumn(name="object_id", referencedColumnName="ID")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="term_taxonomy_id", referencedColumnName="term_taxonomy_id")
     *   }
     * )
     */
    private $taxonomies;

    public function __construct()
    {
        $this->metas      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->taxonomies = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->date            = new \DateTime('now');
        $this->dateGmt         = new \DateTime('now', new \DateTimeZone('GMT'));
        $this->modifiedDate    = new \DateTime('now');
        $this->modifiedDateGmt = new \DateTime('now', new \DateTimeZone('GMT'));
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->modifiedDate     = new \DateTime('now');
        $this->modifiedDateGmt  = new \DateTime('now', new \DateTimeZone('GMT'));
    }

    /**
     * Get ID
     *
     * @return bigint
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set dateGmt
     *
     * @param datetime $dateGmt
     */
    public function setDateGmt($dateGmt)
    {
        $this->dateGmt = $dateGmt;
    }

    /**
     * Get dateGmt
     *
     * @return datetime
     */
    public function getDateGmt()
    {
        return $this->dateGmt;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;

        $this->excerpt = $this->trimContent($content);
    }

    /**
     * Cut string to n symbols and add delim but do not break words.
     *
     * @param string string we are operating with
     * @param integer character count to cut to
     * @param string|NULL delimiter. Default: '...'
     * @return string processed string
     **/
    public function trimContent($content)
    {
        $content = strip_tags($content);
        $length = $this->getExcerptLength();

        if (strlen($content) <= $length) {
            // return origin content if not needed
            return $content;
        }

        $content = substr($content, 0, $length);
        $pos = strrpos($content, " ");

        if ($pos > 0) {
            $content = substr($content, 0, $pos);
        }

        return $content;
    }

    /**
     * Get content
     *
     * @return text
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set title
     *
     * @param text $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return text
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set excerpt
     *
     * @param text $excerpt
     */
    public function setExcerpt($excerpt)
    {
        $this->excerpt = $excerpt;
    }

    /**
     * Get excerpt
     *
     * @return text
     */
    public function getExcerpt()
    {
        return $this->excerpt;
    }

    /**
     * Set excerpt length
     *
     * @param int $excerptLength
     */
    public function setExcerptLength($excerptLength)
    {
        $this->excerptLength = (int)$excerptLength;
    }

    /**
     * Get excerpt length
     *
     * @return int
     */
    public function getExcerptLength()
    {
        return $this->excerptLength;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set commentStatus
     *
     * @param string $commentStatus
     */
    public function setCommentStatus($commentStatus)
    {
        $this->commentStatus = $commentStatus;
    }

    /**
     * Get commentStatus
     *
     * @return string
     */
    public function getCommentStatus()
    {
        return $this->commentStatus;
    }

    /**
     * Set pingStatus
     *
     * @param string $pingStatus
     */
    public function setPingStatus($pingStatus)
    {
        $this->pingStatus = $pingStatus;
    }

    /**
     * Get pingStatus
     *
     * @return string
     */
    public function getPingStatus()
    {
        return $this->pingStatus;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set post slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get post slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set toPing
     *
     * @param text $toPing
     */
    public function setToPing($toPing)
    {
        $this->toPing = $toPing;
    }

    /**
     * Get toPing
     *
     * @return text
     */
    public function getToPing()
    {
        return $this->toPing;
    }

    /**
     * Set pinged
     *
     * @param text $pinged
     */
    public function setPinged($pinged)
    {
        $this->pinged = $pinged;
    }

    /**
     * Get pinged
     *
     * @return text
     */
    public function getPinged()
    {
        return $this->pinged;
    }

    /**
     * Set modifiedDate
     *
     * @param datetime $modifiedDate
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;
    }

    /**
     * Get modifiedDate
     *
     * @return datetime
     */
    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }

    /**
     * Set modifiedDateGmt
     *
     * @param datetime $modifiedDateGmt
     */
    public function setModifiedDateGmt($modifiedDateGmt)
    {
        $this->modifiedDateGmt = $modifiedDateGmt;
    }

    /**
     * Get modifiedDateGmt
     *
     * @return datetime
     */
    public function getModifiedDateGmt()
    {
        return $this->modifiedDateGmt;
    }

    /**
     * Set contentFiltered
     *
     * @param text $contentFiltered
     */
    public function setContentFiltered($contentFiltered)
    {
        $this->contentFiltered = $contentFiltered;
    }

    /**
     * Get contentFiltered
     *
     * @return text
     */
    public function getContentFiltered()
    {
        return $this->contentFiltered;
    }

    /**
     * Set parent
     *
     * @param bigint $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return bigint
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set guid
     *
     * @param string $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * Get guid
     *
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * Set menuOrder
     *
     * @param integer $menuOrder
     */
    public function setMenuOrder($menuOrder)
    {
        $this->menuOrder = $menuOrder;
    }

    /**
     * Get menuOrder
     *
     * @return integer
     */
    public function getMenuOrder()
    {
        return $this->menuOrder;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set commentCount
     *
     * @param bigint $commentCount
     */
    public function setCommentCount($commentCount)
    {
        $this->commentCount = $commentCount;
    }

    /**
     * Get commentCount
     *
     * @return bigint
     */
    public function getCommentCount()
    {
        return $this->commentCount;
    }

    /**
     * Add metas
     *
     * @param Hypebeast\WordpressBundle\Entity\PostMeta $metas
     */
    public function addMeta(\Hypebeast\WordpressBundle\Entity\PostMeta $meta)
    {
        $this->metas[] = $meta;
    }

    /**
     * Get metas
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getMetas()
    {
        return $this->metas;
    }

    /**
     * Get metas by meta key
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getMetasByKey($key)
    {
        return $this->getMetas()->filter(function($meta) use ($key) {
            return $meta->getKey() === $key;
        });
    }

    /**
     * Add comment
     *
     * @param Hypebeast\WordpressBundle\Entity\Comment $comment
     */
    public function addComment(\Hypebeast\WordpressBundle\Entity\Comment $comment)
    {
        $comment->setPost($this);
        $this->comments[] = $comment;
        $this->commentCount = $this->getComments()->count();
    }

    /**
     * Get comments
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set user
     *
     * @param Hypebeast\WordpressBundle\Entity\User $user
     */
    public function setUser(\Hypebeast\WordpressBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Hypebeast\WordpressBundle\Entity\User | null
     */
    public function getUser()
    {
        if($this->user instanceof \Doctrine\ORM\Proxy\Proxy) {
            try {
                // prevent lazy loading the user entity becuase it might not exist
                $this->user->__load();
            } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                // return null if user does not exist
                $this->user = null;
            }
        }

        return $this->user;
    }

    /**
     * Add taxonomies
     *
     * @param Hypebeast\WordpressBundle\Entity\Taxonomy $taxonomy
     */
    public function addTaxonomy(\Hypebeast\WordpressBundle\Entity\Taxonomy $taxonomy)
    {
        $this->taxonomies[] = $taxonomy;
    }

    /**
     * Get taxonomies
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTaxonomies()
    {
        return $this->taxonomies;
    }
}