import React, { Component } from 'react';

export default ({message: {from, text}}) => (
  <div
    className={`message ${from}-message`}
    dangerouslySetInnerHTML={{__html: text}}
  >
  </div>
);
