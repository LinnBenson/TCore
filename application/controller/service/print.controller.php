<?php
    class printController {
        /**
         * 打印所有的定时任务
         */
        public function timer() {
            foreach( task::$timer as $name => $id ) {
                echo getTime()." [ Thread_".task::$thread." ] Active Timer: {$name}\n";
            }
        }
        /**
         * 打印所有的分组信息
         */
        public function group() { $this->printGroup( task::$group ); }
        private function printGroup( $data, $add = '' ) {
            $add = !empty($add) ? "{$add}." : '';
            foreach ( $data as $name => $item ) {
                if ( is_object( $item ) ) {
                    echo getTime() . " [ Thread_" . task::$thread . " ] Group: {$add}{$name} | Type: Object\n";
                }elseif ( is_array( $item ) ) {
                    $this->printGroup($item, "{$add}{$name}");
                }else {
                    echo getTime() . " [ Thread_" . task::$thread . " ] Group: {$add}{$name} | Value: {$item}\n";
                }
            }
        }
    }