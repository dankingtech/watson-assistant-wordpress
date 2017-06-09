import React, { Component } from 'react';

export default ({message: {from, text}}) => (
  <div
    className={`popup-message ${from}-message`}
    dangerouslySetInnerHTML={{__html: text}}
  >
  </div>
);
