<?php

class GlobalUserPageHooks {
	/**
	 * Adds the user option for using GlobalUserpage to Special:GlobalPreferences.
	 *
	 * @param User $user
	 * @param array $preferences Preference descriptions
	 * @return bool
	 */
	public static function onGetPreferences( User $user, &$preferences ) {
		if ( class_exists( 'GlobalPreferences' ) && GlobalPreferences::onGlobalPrefsPage() ) {
			$preferences['globaluserpage'] = array(
				'type' => 'toggle',
				'label-message' => 'globaluserpage-preferences',
				'section' => 'personal/info', // not the best place for it, but eh
			);
		}

		return true;
	}

	/**
	 * @param Title $title
	 * @param Article|null $page
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function onArticleFromTitle( Title &$title, &$page, $context ) {
		if ( $title->inNamespace( NS_USER ) ) {
			$page = new GlobalUserPage( $title );
		}

		return true;
	}

	/**
	 * Turn red links into blue in the navigation tabs (Monobook's p-cactions).
	 *
	 * @param SkinTemplate $sktemplate
	 * @param array $links
	 * @return bool
	 */
	public static function onSkinTemplateNavigationUniversal( &$sktemplate, &$links ) {
		$context = $sktemplate->getContext();
		$title = $sktemplate->getTitle();


		if ( GlobalUserPage::displayGlobalPage( $title ) ) {
			$links['namespaces']['user']['href'] = $title->getFullURL();
			$links['namespaces']['user']['class'] = 'selected';
			//$links['namespaces']['user_talk']['class'] = '';
			//$links['namespaces']['user_talk']['href'] = 'http://www.shoutwiki.com/wiki/User_talk:' . $title->getText();
			/*
			$links['views'] = array(); // Kill the 'Create' button @todo make this suck less
			$links['views'][] = array(
				'class' => false,
				'text' => $context->msg( 'globaluserpage-edit-tab' ),
				'href' => wfAppendQuery(
					'http://www.shoutwiki.com/w/index.php',
					array(
						'action' => 'edit',
						'title' => $title->getPrefixedText()
					)
				)
			);
			*/
		}

		return true;
	}


	/**
	 * Use action=purge to clear cache
	 *
	 * @param $article Article
	 * @return bool
	 */
	public static function onArticlePurge( &$article ) {
		if ( $article instanceof GlobalUserPage ) {
			$article->clearCache();
		}

		return true;
	}

	/**
	 * Turn red User: links into blue ones
	 *
	 * @param $linker Linker
	 * @param $target Title
	 * @param $text String
	 * @param $customAtrribs Array: array of custom attributes [unused]
	 * @param $query [unused]
	 * @param $ret String: return value (link HTML)
	 * @return Boolean
	 */
	public static function brokenLink( $linker, $target, &$text, &$customAttribs, &$query, &$options, &$ret ) {
		if ( GlobalUserPage::displayGlobalPage( $target ) ) {
			if ( in_array( 'known', $options ) || $target->isKnown() ) {
				return true;
			} else {
				$ret = Linker::linkKnown( $target, $text );
				return false;
			}
		}

		return true;
	}

	/**
	 * Update caches after a page is saved
	 *
	 * @param WikiPage $article
	 * @param User $user
	 * @param Content $content
	 * @param string $summary
	 * @param boolean $isMinor
	 * @param boolean $isWatch
	 * @param $section null Deprecated
	 * @param integer $flags
	 * @param Revision|null $revision
	 * @param Status $status
	 * @param integer $baseRevId
	 *
	 * @return bool
	 */
	public static function onPageContentSaveComplete( $article, $user, $content, $summary,
		$isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId
	) {
		$title = $article->getTitle();
		if ( GlobalUserPage::isSourcePage( $title ) ) {
			$page = new GlobalUserPage( $title->getRootTitle() );
			$langCode = GlobalUserPage::getLangCodeForTitle( $title );
			if ( $revision !== null ) {
				$page->updateMap( $langCode, $title->getTouched() );
			}
		}

		return true;
	}

	/**
	 * Update caches if a page is deleted
	 *
	 * @param WikiPage $article
	 * @return bool
	 */
	public static function onArticleDeleteComplete( &$article ) {
		$title = $article->getTitle();
		if ( GlobalUserPage::isSourcePage( $title ) ) {
			$langCode = GlobalUserPage::getLangCodeForTitle( $title );
			$page = new GlobalUserPage( $title->getRootTitle() );
			$page->removeMap( $langCode );

			if ( $langCode === 'em' ) {
				// It's the root userpage, so we need to clear the enabled key
				$page->clearEnabledCache();
			}
		}

	}
}
