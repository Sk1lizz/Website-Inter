const config = {
	mode: 'production',
	entry: {
		index: './src/js/index.js',
		contacts: './src/js/contacts.js',
		playerpage: './src/js/playerpage.js',
		statistics11x11: './src/js/statistics11x11.js',
		playerstatistics: './src/js/playerstatistics.js',
		statistics8x8: './src/js/statistics8x8.js',
		scrollindex: './src/js/scrollindex.js',
		levelplayer: './src/js/levelplayer.js',
		matchlist11x11: './src/js/matchlist11x11.js',
		matchlist8x8: './src/js/matchlist8x8.js',
		Awards: './src/js/awardsplayer.js',
		lostplayer: './src/js/lostplayer.js',
		birthday: './src/js/brithday.js',
		admin: './src/public/js/admin.js',

	},
	output: {
		filename: '[name].bundle.js',
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				use: ['style-loader', 'css-loader'],
			},
		],
	},
};

const webpack = require('webpack');

module.exports = {
	// ...
	resolve: {
		fallback: {
			zlib: require.resolve('browserify-zlib'),
			stream: require.resolve('stream-browserify'),
			path: require.resolve('path-browserify'),
			crypto: require.resolve('crypto-browserify'),
		},
	},
	plugins: [
		new webpack.ProvidePlugin({
			process: 'process/browser',
			Buffer: ['buffer', 'Buffer'],
		}),
	],
};

module.exports = config;