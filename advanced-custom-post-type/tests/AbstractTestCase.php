<?php

namespace ACPT\Tests;

use ACPT\Core\Shortcodes\ACPT\OptionPageMetaShortcode;
use ACPT\Core\Shortcodes\ACPT\PostMetaShortcode;
use ACPT\Core\Shortcodes\ACPT\TaxonomyMetaShortcode;
use ACPT\Core\Shortcodes\ACPT\UserMetaShortcode;
use ACPT\Includes\ACPT_DB;
use ACPT\Utils\Wordpress\Files;
use DOMDocument;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var int
     */
    protected $oldest_page_id;

    /**
     * @var int
     */
    protected $second_oldest_page_id;

    /**
     * @var int
     */
    protected $oldest_post_id;

    /**
     * @var int
     */
    protected $oldest_category_id;

    /**
     * @var int
     */
    protected $oldest_post_tag_id;

	/**
	 * @var int
	 */
    protected $oldest_user_id;

    /**
     * set up the server
     */
    public function setUp(): void
    {
        parent::setUp();

        ACPT_DB::flushCache();

        $this->oldest_page_id = $this->getPostId('page', 0);
        $this->oldest_post_id = $this->getPostId('post', 0);
        $this->second_oldest_page_id = $this->getPostId('page', 1);
        $this->oldest_category_id = $this->getTermId('category', 0);
        $this->oldest_post_tag_id = $this->getTermId('post_tag', 0);
        $this->oldest_user_id = $this->getUserId(0);

        $this->includeFuncions();
        $this->setCurrentUser();
        $this->initShortcode();
    }

	/**
	 * include PHP functions
	 */
    private function includeFuncions()
    {
	    include_once __DIR__ . '/../functions/acpt_functions.php';
    }

    /**
     * Set the current user (just for test purpose)
     */
    private function setCurrentUser()
    {
        $users = get_users(['role__in' => ['administrator']]);

        wp_set_current_user($users[0]->ID);
    }

    /**
     * init the acpt shortcode
     */
    private function initShortcode()
    {
        add_shortcode('acpt', [new PostMetaShortcode(), 'render']);
        add_shortcode('acpt_tax', [new TaxonomyMetaShortcode(), 'render']);
        add_shortcode('acpt_user', [new UserMetaShortcode(), 'render']);
        add_shortcode('acpt_option', [new OptionPageMetaShortcode(), 'render']);
    }

    /**
     * @param string $postType
     * @param int    $index
     *
     * @return int|null
     */
	protected function getPostId($postType, $index)
    {
        $numberposts = $index + 1;
        $oldest_id_query = get_posts("post_type=".$postType."&numberposts=".$numberposts."&order=ASC");

        if(empty($oldest_id_query) or empty($oldest_id_query[$index])){
           return null;
        }

        return $oldest_id_query[$index]->ID;
    }

    /**
     * @param $term
     * @param $index
     * @return mixed
     */
	protected function getTermId($term, $index)
    {
        $terms = get_terms( $term, [
            'hide_empty' => false,
        ] );

        if(isset($terms[$index])){
            return $terms[$index]->term_id;
        }

        return null;
    }

	/**
	 * @param $index
	 *
	 * @return mixed|null
	 */
    protected function getUserId($index)
    {
	    $users = get_users([
		    'fields' => [
			    'ID',
			    'display_name',
		    ]
	    ]);

	    foreach ($users as $i => $user){
	    	if($i === $index){
	    		return (int)$user->ID;
		    }
	    }

	    return null;
    }

    /**
     * @param $url
     * @return bool
     */
    protected function deleteFile($url)
    {
        return Files::deleteFile($url);
    }

    /**
     * @param $path
     * @param null $parentPostId
     * @return array
     */
    protected function uploadFile($path, $parentPostId = null)
    {
        return Files::uploadFile($path, null, $parentPostId);
    }

	/**
	 * @return array
	 */
    protected function dataProvider()
    {
    	return [
		    'post_id' => $this->oldest_page_id,
		    'term_id' => $this->oldest_category_id,
		    'user_id' => $this->oldest_user_id,
		    'option_page' => 'new-page',
	    ];
    }

	/**
	 * @param string $html
	 *
	 * @return DOMDocument
	 */
    protected function parseHtml(string $html): DOMDocument
    {
	    $dom = new DOMDocument;
	    $dom->loadHTML($html);

	    return $dom;
    }

    /**
     * @param array $expected
     * @param $actual
     * @param string $message
     */
    public function assertOneOf( array $expected, $actual, $message = '' )
    {
        $constraints = [];

        foreach ( $expected as $expectedValue ) {
            $constrains[] = new IsIdentical( $expectedValue );
        }

        $constraint = new LogicalOr();
        $constraint->setConstraints( $constraints );

        $this->assertThat( $actual, $constraint, $message );
    }

    /**
     * @param $expected
     * @param $actual
     */
    public function assertTimeIsEqualsTo($expected, $actual)
    {
        $timeFormat = get_option('time_format');
        $exp = date_i18n( $timeFormat, $expected);
        $act = date_i18n( $timeFormat, $actual);

        $this->assertEquals($exp, $act);
    }

    /**
     * @param $expected
     * @param $actual
     */
    public function assertDateIsEqualsTo($expected, $actual)
    {
        $timeFormat = get_option('date_format');
        $exp = date_i18n( $timeFormat, $expected);
        $act = date_i18n( $timeFormat, $actual);

        $this->assertEquals($exp, $act);
    }
}