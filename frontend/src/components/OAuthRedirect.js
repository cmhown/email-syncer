// src/components/OAuthRedirect.js
import React,  { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../api';

const OAuthRedirect = () => {
  const navigate = useNavigate();
  const {provider} = useParams();

    useEffect(
        () => {
            handleOAuthLogin();
        },
        [provider]
    );

  const handleOAuthLogin = async () => {
    try {
      const response = await api.get(`auth/${provider}/redirect`);
      const { authorization_url } = response.data;

      // Redirect to the OAuth provider's authorization URL
      window.location.href = authorization_url;
    } catch (error) {
      console.error('Error during OAuth redirect:', error);
      // navigate('/error');
    }
  };

  return (
    <div>
      <h2>Redirecting to {provider} login...</h2>
    </div>
  );
};

export default OAuthRedirect;
