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

module.exports = config;