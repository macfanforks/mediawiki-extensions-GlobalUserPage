<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Job class that submits LocalGlobalUserPageCacheUpdateJob jobs
 */
class GlobalUserPageLocalJobSubmitJob extends Job {
	public function __construct( Title $title, array $params ) {
		parent::__construct( 'GlobalUserPageLocalJobSubmitJob', $title, $params );
	}

	public function run() {
		$job = new LocalGlobalUserPageCacheUpdateJob(
			Title::newFromText( 'User:' . $this->params['username'] ),
			$this->params
		);
		foreach ( GlobalUserPage::getEnabledWikis() as $wiki ) {
			JobQueueGroup::singleton( $wiki )->push( $job );
		}
	}
}
