import React, { Component } from 'react';

export default ({message: {from, text}}) => (
  <div
    className={`message ${from}-message watson-font`}
    dangerouslySetInnerHTML={{__html: text}}
  >
  </div>
);
