<?php

class DeclFilter_KeyPatternFilterTest extends PHPUnit_Framework_TestCase
{
	function testMatch()
	{
		$rule = new DeclFilter_KeyPatternFilterRule( array(
			'/^foo_\d+$/' => 'digits',
			'/^bar_[a-z]+$/' => 'digits',
		) );

		$this->assertTrue( $rule->match( 'foo_123' ) );
		$this->assertTrue( $rule->match( 'bar_abc' ) );
		$this->assertFalse( $rule->match( 'foo_abc' ) );
		$this->assertFalse( $rule->match( 'baz' ) );
	}

	function testApply()
	{
		$rule = new DeclFilter_KeyPatternFilterRule( array(
			'/^foo_\d+$/' => 'digits',
			'/^bar_[a-z]+$/' => 'alpha',
		) );

		$data = array(
			'foo_123' => '123abc',
			'bar_abc' => '123abc',
			'foo' => '123abc',
		);

		$rule->apply( $data, 'foo_123' );
		$rule->apply( $data, 'bar_abc' );

		$this->assertEquals( $data['foo_123'], '123' );
		$this->assertEquals( $data['bar_abc'], 'abc' );
		$this->assertEquals( $data['foo'], '123abc' );
	}

	function testApplyOnElements()
	{
		$rule = new DeclFilter_KeyPatternFilterRule( array(
			'/^foo_\d+$/' => 'digits',
		) );
		$rule->applyOnElements();

		$data = array(
			'foo_123' => array( '123abc', '456def' ),
		);

		$rule->apply( $data, 'foo_123' );

		$this->assertEquals( $data['foo_123'], array( '123', '456' ) );
	}
}

?>
