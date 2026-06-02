<?php
/**
 * Tests for decay scoring logic.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector\Tests;

use ContentDecayDetector\Scanner;
use ContentDecayDetector\Settings;
use ContentDecayDetector\PostSnapshot;
use PHPUnit\Framework\TestCase;

/**
 * Class ScorerTest
 *
 * Tests the decay score calculation logic in Scanner.
 */
class ScorerTest extends TestCase {

    /**
     * Scanner instance.
     *
     * @var Scanner
     */
    private Scanner $scanner;

    /**
     * Set up test dependencies.
     *
     * @return void
     */
    protected function setUp(): void {
        $this->scanner = new Scanner(
            $this->createMock( Settings::class ),
            $this->createMock( PostSnapshot::class )
        );
    }

    /**
     * Test that score is 100 when no previous snapshot exists.
     *
     * @return void
     */
    public function test_score_is_100_when_no_previous_snapshot(): void {
        $score = $this->scanner->calculate_decay_score( 500, null );
        $this->assertSame( 100, $score );
    }

    /**
     * Test that score is 100 when traffic is unchanged.
     *
     * @return void
     */
    public function test_score_is_100_when_traffic_unchanged(): void {
        $previous = [ 'traffic_score' => 500 ];
        $score    = $this->scanner->calculate_decay_score( 500, $previous );
        $this->assertSame( 100, $score );
    }

    /**
     * Test that score is 50 when traffic has halved.
     *
     * @return void
     */
    public function test_score_is_50_when_traffic_halved(): void {
        $previous = [ 'traffic_score' => 1000 ];
        $score    = $this->scanner->calculate_decay_score( 500, $previous );
        $this->assertSame( 50, $score );
    }

    /**
     * Test that score is capped at 100 when traffic has increased.
     *
     * @return void
     */
    public function test_score_is_capped_at_100_when_traffic_increased(): void {
        $previous = [ 'traffic_score' => 500 ];
        $score    = $this->scanner->calculate_decay_score( 1000, $previous );
        $this->assertSame( 100, $score );
    }

    /**
     * Test that score is 0 when traffic has dropped to zero.
     *
     * @return void
     */
    public function test_score_is_0_when_traffic_is_zero(): void {
        $previous = [ 'traffic_score' => 1000 ];
        $score    = $this->scanner->calculate_decay_score( 0, $previous );
        $this->assertSame( 0, $score );
    }

    /**
     * Test that score is 100 when previous traffic score is zero.
     *
     * @return void
     */
    public function test_score_is_100_when_previous_traffic_is_zero(): void {
        $previous = [ 'traffic_score' => 0 ];
        $score    = $this->scanner->calculate_decay_score( 500, $previous );
        $this->assertSame( 100, $score );
    }
}