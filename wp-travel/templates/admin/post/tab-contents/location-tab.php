<div class="map-wrap">
  <input id="search-input" class="controls" type="text" placeholder="Enter a location">
  <div id="gmap" style="width:100%;height:300px"></div>
</div>
<style>
.map-wrap{
  position: relative;
}
.controls {
    margin-top: 10px;
    border: 1px solid transparent;
    border-radius: 2px 0 0 2px;
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    height: 32px;
    outline: none;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}
#search-input {
  background-color: #fff;
  font-family: Roboto;
  font-size: 15px;
  font-weight: 300;
  margin-left: 12px;
  padding: 0 11px 0 13px;
  text-overflow: ellipsis;
  width: 67%;
  position: absolute;
  top: 0px;
  z-index: 1;
  left: 165px;
}
#search-input:focus {
    border-color: #4d90fe;
}
</style>
