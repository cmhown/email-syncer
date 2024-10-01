// src/App.js
import React from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import Register from './components/Register';
import Dashboard from './components/Dashboard';
import OAuthRedirect from './components/OAuthRedirect';
import OAuthCallback from './components/OAuthCallback';
import ErrorPage from './components/ErrorPage';
import { AuthProvider } from './AuthContext';
import PrivateRoute from './PrivateRoute';
import Login from './components/Login';
import Logout from './components/Logout';

function App() {
  return (
    <AuthProvider>
      <Router>
        <div>
          <h1 style={{ textAlign: "center" }}>Emails Sync App</h1>
          <br></br>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/logout" element={<Logout />} />
            <Route
              path="/"
              element={
                <PrivateRoute>
                  <Dashboard />
                </PrivateRoute>
              }
            />
            <Route path="/auth/:provider/redirect" element={<OAuthRedirect />} />
            <Route path="/auth/:provider/callback" element={<OAuthCallback />} />
            <Route path="/error" element={<ErrorPage />} />
            {/* Add more routes as needed */}
          </Routes>
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;
