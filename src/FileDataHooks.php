<?php
use MediaWiki\MediaWikiServices;

class FileDataHooks {
	/** @const array[] ATTRS Allowed attributes from different tables. */
	private const ATTRS = [
		'image' => [
			'name' => 'img_name',
			'size' => 'img_size',
			'width' => 'img_width',
			'height' => 'img_height',
			'bits' => 'img_bits',
			'media_type' => 'img_media_type',
			'major_mime' => 'img_major_mime',
			'minor_mime' => 'img_minor_mime',
			'timestamp' => 'img_timestamp',
			'sha1' => 'img_sha1'
		],
		'comment' => [
			'description' => 'comment_text'
		],
		'actor' => [
			'user' => 'actor_user',
			'user_text' => 'actor_name'
		]
	];
	
	/** @const string[] JOINS Join conditions for tables. */
	private const JOINS = [
		'comment' => 'image.img_description_id = comment.comment_id',
		'actor' => 'image.img_actor = actor.actor_id'
	];

	/*
	 * Register {{#file_data:}}.
	 * @param Parser $parser
	 * @return void
	 */
	public static function onParserFirstCallInit( Parser $parser ): void {
		// Create a function hook associating the <code>image_data</code>
		// magic word with fileData()
		$parser->setFunctionHook( 'file_data', [ self::class, 'fileData' ] );
	}

	/*
	 * Render the output of {{#file_data:}}.
	 * @param Parser $parser
	 * @param string $filename
	 * @param string $attr
	 * @return string
	 */
	public static function fileData( Parser $parser, string $filename, string $attr ): string {
		// Whence the required attribute comes, if at all:
		$join = null;
		$field = 'img_metadata';
		foreach ( self::ATTRS as $table => $fields ) {
			if ( isset( $fields[$attr] ) ) {
				$join = $table === 'image' ? null : $table;
				$field = $fields[$attr];
				break;
			}
		}

		$title = Title::newFromText( $filename, NS_FILE );
		if ( !$title ) {
			return self::error ('invalid', $filename);
		}
		if ( !$title->exists() ) {
			return self::error ('absent', $filename);
		}
		
		// Build and execute a query:
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		$query = $dbr->newSelectQueryBuilder()
			->select( [ $field ] )
			->from( 'image' );
		if ( $join ) {
			$query = $query->join( $join, null, self::JOINS[$join] );
		}
		$query = $query
			->where( $dbr->expr( 'img_name', '=', $title->getDBkey() ) )
			->limit( 1 )
			->caller( __METHOD__ );
		$value = $query->fetchField();

		// Process metadata:
		if ( $field === 'img_metadata' ) {
			$value = json_decode( $value ?? '{}', true)[$attr] ?? null;
		}
		return $value ?? self::error ('illegal', $attr);
	}
	
	/**
	 * Form an error message.
	 * @param string $id Message id.
	 * @return string
	 */
	private static function error( string $id, ...$optional ): string {
		return '<span class="error">'
			. wfMessage( "filedata-$id", $optional )->text()
			. '</span>';
	}
}
