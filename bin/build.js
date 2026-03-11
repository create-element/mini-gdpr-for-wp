#!/usr/bin/env node
/**
 * Mini WP GDPR — JavaScript build script.
 *
 * Minifies all plugin JavaScript files using Terser and writes the output to
 * assets/<name>.min.js alongside a source map at assets/<name>.min.js.map.
 *
 * Usage:
 *   node bin/build.js           # Build all files once
 *   npm run build               # Same, via npm
 *
 * Source maps are excluded from git (see .gitignore). The .min.js files are
 * committed to allow plugin installation without a build step.
 */

'use strict';

const fs   = require( 'fs' );
const path = require( 'path' );

// Terser is installed globally via npm/nvm. Resolve from the global
// node_modules when NODE_PATH is not already set.
let terserModule;
try {
	terserModule = require( 'terser' );
} catch ( e ) {
	const { execSync } = require( 'child_process' );
	const globalRoot   = execSync( 'npm root -g', { encoding: 'utf8' } ).trim();
	terserModule       = require( path.join( globalRoot, 'terser' ) );
}
const { minify } = terserModule;

// ---------------------------------------------------------------------------
// Files to minify
// ---------------------------------------------------------------------------

const ROOT       = path.resolve( __dirname, '..' );
const ASSETS_DIR = path.join( ROOT, 'assets' );

/** @type {string[]} Base filenames (without extension) to process. */
const JS_FILES = [
	'mini-gdpr',
	'mini-gdpr-cookie-popup',
	'mini-gdpr-admin',
	'mini-gdpr-admin-cf7',
];

// ---------------------------------------------------------------------------
// Build
// ---------------------------------------------------------------------------

( async function build() {
	let hasError = false;

	for ( const name of JS_FILES ) {
		const srcFile  = path.join( ASSETS_DIR, `${ name }.js` );
		const outFile  = path.join( ASSETS_DIR, `${ name }.min.js` );
		const mapFile  = path.join( ASSETS_DIR, `${ name }.min.js.map` );
		const mapBasename = `${ name }.min.js.map`;

		if ( ! fs.existsSync( srcFile ) ) {
			console.warn( `  [skip]  ${ name }.js — file not found` );
			continue;
		}

		const source = fs.readFileSync( srcFile, 'utf8' );

		try {
			const result = await minify(
				{ [ `${ name }.js` ]: source },
				{
					compress: {
						drop_console: false, // Keep console.error() in admin scripts.
						passes:       2,
					},
					mangle: true,
					sourceMap: {
						filename: `${ name }.min.js`,
						url:      mapBasename,
					},
				}
			);

			fs.writeFileSync( outFile, result.code, 'utf8' );

			if ( result.map ) {
				fs.writeFileSync( mapFile, result.map, 'utf8' );
			}

			const srcSize = Buffer.byteLength( source,      'utf8' );
			const outSize = Buffer.byteLength( result.code, 'utf8' );
			const saving  = Math.round( ( 1 - outSize / srcSize ) * 100 );

			console.log( `  [ok]    ${ name }.js → ${ name }.min.js  (${ srcSize }b → ${ outSize }b, -${ saving }%)` );
		} catch ( err ) {
			console.error( `  [fail]  ${ name }.js — ${ err.message }` );
			hasError = true;
		}
	}

	if ( hasError ) {
		process.exit( 1 );
	}

	console.log( '\nBuild complete.' );
} )();
