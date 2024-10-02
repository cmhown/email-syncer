// src/components/OAuthCallback.js
import React, { useEffect } from 'react';
import { useLocation, useNavigate, useParams } from 'react-router-dom';
import api from '../api';

const OAuthCallback = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const {provider} = useParams();

  useEffect(() => {
    const fetchOAuthToken = async () => {
      const query = new URLSearchParams(location.search);
      const code = query.get('code'); 

      if (code && provider) {
        try {
          const response = await api.post(`/auth/${provider}/callback`, { code });

          // Navigate to the dashboard or another page after successful authentication
          navigate('/');
        } catch (error) {
          console.error('Error during OAuth callback:', error);
        }
      } else {
        // navigate('/error');
      }
    };

    fetchOAuthToken();
  }, [location, navigate]);

  return <div>Loading...</div>;
};

export default OAuthCallback;
