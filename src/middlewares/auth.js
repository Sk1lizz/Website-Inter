function requireAuth(req, res, next) {
    if (req.session && req.session.user === 'admin') {
        next();
    } else {
        res.redirect('/login');
    }
}

function requireApiAuth(req, res, next) {
    if (req.session && req.session.user === 'admin') {
        next();
    } else {
        res.status(401).json({ error: 'Не авторизован' });
    }
}

module.exports = { requireAuth, requireApiAuth };