import React, { Component } from 'react';


export default ({sendMessage, message: {from, text, options}}) => {
  return <div>
    <div
      className={`message ${from}-message watson-font`}
      dangerouslySetInnerHTML={{__html: text}}
    ></div>
    {
      Array.isArray(options) && options.map((option, index) => (
        <div className={`message message-option watson-font`} onClick={() => { sendMessage(option); }}>
          {option}
        </div>
      ))
    }
  </div>;
}
